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
use Fusio\Impl\Event\Rate\CreatedEvent;
use Fusio\Impl\Event\Rate\DeletedEvent;
use Fusio\Impl\Event\Rate\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\RateAllocation;
use Fusio\Model\Backend\RateCreate;
use Fusio\Model\Backend\RateUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Rate
{
    public function __construct(
        private Table\Rate $rateTable,
        private Table\Rate\Allocation $rateAllocationTable,
        private Rate\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(RateCreate $rate, UserContext $context): int
    {
        $this->validator->assert($rate, $context->getTenantId());

        try {
            $this->rateTable->beginTransaction();

            $row = new Table\Generated\RateRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Rate::STATUS_ACTIVE);
            $row->setPriority($rate->getPriority());
            $row->setName($rate->getName());
            $row->setRateLimit($rate->getRateLimit());
            $row->setTimespan((string) $rate->getTimespan());
            $row->setMetadata($rate->getMetadata() !== null ? Parser::encode($rate->getMetadata()) : null);
            $this->rateTable->create($row);

            $rateId = $this->rateTable->getLastInsertId();
            $rate->setId($rateId);

            $this->handleAllocations($rateId, $rate->getAllocation());

            $this->rateTable->commit();
        } catch (\Throwable $e) {
            $this->rateTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($rate, $context));

        return $rateId;
    }

    public function update(string $rateId, RateUpdate $rate, UserContext $context): int
    {
        $existing = $this->rateTable->findOneByIdentifier($context->getTenantId(), $rateId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find rate');
        }

        if ($existing->getStatus() == Table\Rate::STATUS_DELETED) {
            throw new StatusCode\GoneException('Rate was deleted');
        }

        $this->validator->assert($rate, $context->getTenantId(), $existing);

        try {
            $this->rateTable->beginTransaction();

            $existing->setPriority($rate->getPriority() ?? $existing->getPriority());
            $existing->setName($rate->getName() ?? $existing->getName());
            $existing->setRateLimit($rate->getRateLimit() ?? $existing->getRateLimit());
            $existing->setTimespan($rate->getTimespan() ?? $existing->getTimespan());
            $existing->setMetadata($rate->getMetadata() !== null ? Parser::encode($rate->getMetadata()) : $existing->getMetadata());
            $this->rateTable->update($existing);

            $this->handleAllocations($existing->getId(), $rate->getAllocation());

            $this->rateTable->commit();
        } catch (\Throwable $e) {
            $this->rateTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($rate, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $rateId, UserContext $context): int
    {
        $existing = $this->rateTable->findOneByIdentifier($context->getTenantId(), $rateId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find rate');
        }

        if ($existing->getStatus() == Table\Rate::STATUS_DELETED) {
            throw new StatusCode\GoneException('Rate was deleted');
        }

        $existing->setStatus(Table\Rate::STATUS_DELETED);
        $this->rateTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }

    /**
     * @param RateAllocation[] $allocations
     */
    private function handleAllocations(int $rateId, ?array $allocations = null): void
    {
        $this->rateAllocationTable->deleteAllFromRate($rateId);

        if (!empty($allocations)) {
            foreach ($allocations as $allocation) {
                if ($allocation->getAuthenticated() === true) {
                    $authenticated = 1;
                } elseif ($allocation->getAuthenticated() === false) {
                    $authenticated = 0;
                } else {
                    $authenticated = null;
                }

                $row = new Table\Generated\RateAllocationRow();
                $row->setRateId($rateId);
                $row->setOperationId($allocation->getOperationId());
                $row->setUserId($allocation->getUserId());
                $row->setPlanId($allocation->getPlanId());
                $row->setAppId($allocation->getAppId());
                $row->setAuthenticated($authenticated);
                $this->rateAllocationTable->create($row);
            }
        }
    }
}
