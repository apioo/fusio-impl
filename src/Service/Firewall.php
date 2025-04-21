<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Firewall\CreatedEvent;
use Fusio\Impl\Event\Firewall\DeletedEvent;
use Fusio\Impl\Event\Firewall\UpdatedEvent;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Table;
use Fusio\Model\Backend\FirewallCreate;
use Fusio\Model\Backend\FirewallUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Sql\Condition;

/**
 * Firewall
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Firewall
{
    public function __construct(
        private Table\Firewall $firewallTable,
        private Firewall\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(FirewallCreate $firewall, UserContext $context): int
    {
        $this->validator->assert($firewall, $context->getTenantId());

        try {
            $this->firewallTable->beginTransaction();

            $row = new Table\Generated\FirewallRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Firewall::STATUS_ACTIVE);
            $row->setName($firewall->getName() ?? throw new StatusCode\BadRequestException('Provided no name'));
            $row->setType($firewall->getType() ?? throw new StatusCode\BadRequestException('Provided no type'));
            $row->setIp(inet_ntop($firewall->getIp() ?? throw new StatusCode\BadRequestException('Provided no IP')));
            $row->setMask($firewall->getMask() ?? throw new StatusCode\BadRequestException('Provided no mask'));
            $row->setExpire($firewall->getExpire());
            $row->setMetadata($firewall->getMetadata() !== null ? Parser::encode($firewall->getMetadata()) : null);
            $this->firewallTable->create($row);

            $firewallId = $this->firewallTable->getLastInsertId();
            $firewall->setId($firewallId);

            $this->firewallTable->commit();
        } catch (\Throwable $e) {
            $this->firewallTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($firewall, $context));

        return $firewallId;
    }

    public function update(string $firewallId, FirewallUpdate $firewall, UserContext $context): int
    {
        $existing = $this->firewallTable->findOneByIdentifier($context->getTenantId(), $firewallId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find firewall');
        }

        if ($existing->getStatus() == Table\Form::STATUS_DELETED) {
            throw new StatusCode\GoneException('Firewall was deleted');
        }

        $this->validator->assert($firewall, $context->getTenantId(), $existing);

        try {
            $this->firewallTable->beginTransaction();

            $existing->setName($firewall->getName() ?? $existing->getName());
            $existing->setType($firewall->getType() ?? $existing->getType());
            $existing->setIp($firewall->getIp() !== null ? inet_ntop($firewall->getIp()) : $existing->getIp());
            $existing->setMask($firewall->getMask() ?? $existing->getMask());
            $existing->setExpire($firewall->getExpire() ?? $existing->getExpire());
            $existing->setMetadata($firewall->getMetadata() !== null ? Parser::encode($firewall->getMetadata()) : $existing->getMetadata());
            $this->firewallTable->update($existing);

            $this->firewallTable->commit();
        } catch (\Throwable $e) {
            $this->firewallTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($firewall, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $firewallId, UserContext $context): int
    {
        $existing = $this->firewallTable->findOneByIdentifier($context->getTenantId(), $firewallId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find firewall');
        }

        if ($existing->getStatus() == Table\Firewall::STATUS_DELETED) {
            throw new StatusCode\GoneException('Firewall was deleted');
        }

        try {
            $this->firewallTable->beginTransaction();

            $existing->setStatus(Table\Firewall::STATUS_DELETED);
            $this->firewallTable->update($existing);

            $this->firewallTable->commit();
        } catch (\Throwable $e) {
            $this->firewallTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    public function createTemporaryBanForIP(?string $tenantId, string $ip, \DateInterval $expires): void
    {
        $value = inet_pton($ip);
        if ($value === false) {
            throw new StatusCode\BadRequestException('Provided an invalid IP');
        }

        $now = new \DateTime();
        $now->add($expires);

        $row = new Table\Generated\FirewallRow();
        $row->setTenantId($tenantId);
        $row->setStatus(Table\Firewall::STATUS_ACTIVE);
        $row->setName('Ban-' . str_replace(['.', ':'], '-', $ip) . '-' . $now->format('YmdHis'));
        $row->setType(Table\Firewall::TYPE_DENY);
        $row->setIp($value);
        $row->setMask(strlen($value) === 4 ? 32 : 128);
        $row->setExpire(LocalDateTime::from($now));
        $this->firewallTable->create($row);
    }

    public function isAllowed(string $ip, Context $context): bool
    {
        $value = inet_pton($ip);
        if ($value === false) {
            throw new StatusCode\BadRequestException('Could not convert IP address to its packed in_addr representation');
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\FirewallTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->raw('(' . Table\Generated\FirewallTable::COLUMN_IP . ' & (1 << ' . Table\Generated\FirewallTable::COLUMN_MASK . ')) = (:ip & (1 << ' . Table\Generated\FirewallTable::COLUMN_MASK . '))', ['ip' => $value]);
        $condition->add(Condition::withOr()
            ->nil(Table\Generated\FirewallTable::COLUMN_EXPIRE)
            ->greater(Table\Generated\FirewallTable::COLUMN_EXPIRE, LocalDateTime::now()->toDateTime()->format('Y-m-d H:i:s'))
        );
        $result = $this->firewallTable->findAll($condition);

        $allowed = true;
        foreach ($result as $rule) {
            if ($rule->getType() === Table\Firewall::TYPE_ALLOW) {
                $allowed = true;
                break;
            } else if ($rule->getType() === Table\Firewall::TYPE_DENY) {
                $allowed = false;
            }
        }

        return $allowed;
    }
}
