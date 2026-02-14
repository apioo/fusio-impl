<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Service\Taxonomy;

use Fusio\Impl\Service\Tenant\UsageLimiter;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\TaxonomyRow;
use Fusio\Model\Backend\Taxonomy;
use PSX\Http\Exception as StatusCode;

/**
 * Mover
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Mover
{
    public function __construct(
        private Table\Operation $operationTable,
        private Table\Action $actionTable,
        private Table\Event $eventTable,
        private Table\Cronjob $cronjobTable,
        private Table\Trigger $triggerTable,
    ) {
    }

    public function moveOperation(?string $tenantId, int $categoryId, int $operationId, TaxonomyRow $taxonomyRow): void
    {
        $operationRow = $this->operationTable->findOneByTenantAndId($tenantId, $categoryId, $operationId);
        if (!$operationRow instanceof Table\Generated\OperationRow) {
            throw new StatusCode\BadRequestException('Provided an invalid operation id: ' . $operationId);
        }

        $operationRow->setTaxonomyId($taxonomyRow->getId());
        $this->operationTable->update($operationRow);
    }

    public function moveAction(?string $tenantId, int $categoryId, int $actionId, TaxonomyRow $taxonomyRow): void
    {
        $actionRow = $this->actionTable->findOneByTenantAndId($tenantId, $categoryId, $actionId);
        if (!$actionRow instanceof Table\Generated\ActionRow) {
            throw new StatusCode\BadRequestException('Provided an invalid action id: ' . $actionId);
        }

        $actionRow->setTaxonomyId($taxonomyRow->getId());
        $this->actionTable->update($actionRow);
    }

    public function moveEvent(?string $tenantId, int $categoryId, int $eventId, TaxonomyRow $taxonomyRow): void
    {
        $eventRow = $this->eventTable->findOneByTenantAndId($tenantId, $categoryId, $eventId);
        if (!$eventRow instanceof Table\Generated\EventRow) {
            throw new StatusCode\BadRequestException('Provided an invalid event id: ' . $eventId);
        }

        $eventRow->setTaxonomyId($taxonomyRow->getId());
        $this->eventTable->update($eventRow);
    }

    public function moveCronjob(?string $tenantId, int $categoryId, int $cronjobId, TaxonomyRow $taxonomyRow): void
    {
        $cronjobRow = $this->cronjobTable->findOneByTenantAndId($tenantId, $categoryId, $cronjobId);
        if (!$cronjobRow instanceof Table\Generated\CronjobRow) {
            throw new StatusCode\BadRequestException('Provided an invalid cronjob id: ' . $cronjobId);
        }

        $cronjobRow->setTaxonomyId($taxonomyRow->getId());
        $this->cronjobTable->update($cronjobRow);
    }

    public function moveTrigger(?string $tenantId, int $categoryId, int $triggerId, TaxonomyRow $taxonomyRow): void
    {
        $triggerRow = $this->triggerTable->findOneByTenantAndId($tenantId, $categoryId, $triggerId);
        if (!$triggerRow instanceof Table\Generated\TriggerRow) {
            throw new StatusCode\BadRequestException('Provided an invalid trigger id: ' . $triggerId);
        }

        $triggerRow->setTaxonomyId($taxonomyRow->getId());
        $this->triggerTable->update($triggerRow);
    }
}
