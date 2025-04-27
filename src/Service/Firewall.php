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
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model\Backend\FirewallCreate;
use Fusio\Model\Backend\FirewallUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Http\Exception\TooManyRequestsException;
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
        private Table\Firewall\Log $firewallLogTable,
        private Firewall\Validator $validator,
        private FrameworkConfig $frameworkConfig,
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
            $row->setIp($firewall->getIp() ?? throw new StatusCode\BadRequestException('Provided no IP'));
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
            $existing->setIp($firewall->getIp() ?? $existing->getIp());
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

    /**
     * Throws an exception in case a ban exists for the provided ip
     */
    public function assertAllowed(string $ip, ?string $tenantId): void
    {
        $ipWhitelist = $this->frameworkConfig->getFirewallIgnoreIp();
        if (!empty($ipWhitelist) && in_array($ip, $ipWhitelist)) {
            return;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\FirewallTable::COLUMN_TENANT_ID, $tenantId);
        $condition->equals(Table\Generated\FirewallTable::COLUMN_IP, $ip);
        $condition->equals(Table\Generated\FirewallTable::COLUMN_TYPE, Table\Firewall::TYPE_DENY);
        $condition->add(Condition::withOr()
            ->nil(Table\Generated\FirewallTable::COLUMN_EXPIRE)
            ->greater(Table\Generated\FirewallTable::COLUMN_EXPIRE, LocalDateTime::now()->toDateTime()->format('Y-m-d H:i:s'))
        );

        if ($this->firewallTable->getCount($condition) > 0) {
            throw new TooManyRequestsException('Your IP has sent to many requests please try again later', 60 * 5);
        }
    }

    /**
     * fail2ban logic, in case a user has triggered too many client error responses in the last 10 minutes, we insert a ban for this IP
     * for 10 minutes, this protects us from bruteforce attacks and other malicious requests
     */
    public function handleClientErrorResponse(string $ip, int $responseCode, ?string $tenantId): void
    {
        $count = $this->firewallLogTable->getResponseCodeCount($tenantId, $ip, $this->frameworkConfig->getFirewallFindTime());
        if ($count > $this->frameworkConfig->getFirewallMaxRetry()) {
            $now = new \DateTime();
            $now->add($this->frameworkConfig->getFirewallBanTime());

            $row = new Table\Generated\FirewallRow();
            $row->setTenantId($tenantId);
            $row->setStatus(Table\Firewall::STATUS_ACTIVE);
            $row->setName('Ban-' . str_replace(['.', ':'], '-', $ip) . '-' . $now->format('YmdHis'));
            $row->setType(Table\Firewall::TYPE_DENY);
            $row->setIp($ip);
            $row->setExpire(LocalDateTime::from($now));
            $this->firewallTable->create($row);
        } else {
            $whitelistCodes = $this->frameworkConfig->getFirewallCodes();
            if (!empty($whitelistCodes) && !in_array($responseCode, $whitelistCodes)) {
                return;
            }

            $log = new Table\Generated\FirewallLogRow();
            $log->setTenantId($tenantId);
            $log->setIp($ip);
            $log->setResponseCode($responseCode);
            $log->setInsertDate(LocalDateTime::now());
            $this->firewallLogTable->create($log);
        }
    }
}
