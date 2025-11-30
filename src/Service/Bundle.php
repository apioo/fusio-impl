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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Bundle\CreatedEvent;
use Fusio\Impl\Event\Bundle\DeletedEvent;
use Fusio\Impl\Event\Bundle\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\BundleCreate;
use Fusio\Model\Backend\BundleUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Bundle
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Bundle
{
    public function __construct(
        private Table\Bundle $bundleTable,
        private Bundle\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(BundleCreate $bundle, UserContext $context): int
    {
        $this->validator->assert($bundle, $context->getTenantId());

        try {
            $this->bundleTable->beginTransaction();

            // create bundle
            $row = new Table\Generated\BundleRow();
            $row->setTenantId($context->getTenantId());
            $row->setStatus(Table\Rate::STATUS_ACTIVE);
            $row->setName($bundle->getName() ?? throw new StatusCode\BadRequestException('Provided no bundle name'));
            $row->setConfig(Parser::encode($bundle->getConfig()));
            $this->bundleTable->create($row);

            $bundleId = $this->bundleTable->getLastInsertId();
            $bundle->setId($bundleId);

            $this->bundleTable->commit();
        } catch (\Throwable $e) {
            $this->bundleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($bundle, $context));

        return $bundleId;
    }

    public function update(string $bundleId, BundleUpdate $bundle, UserContext $context): int
    {
        $existing = $this->bundleTable->findOneByIdentifier($context->getTenantId(), $bundleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find bundle');
        }

        if ($existing->getStatus() == Table\Bundle::STATUS_DELETED) {
            throw new StatusCode\GoneException('Bundle was deleted');
        }

        $this->validator->assert($bundle, $context->getTenantId(), $existing);

        try {
            $this->bundleTable->beginTransaction();

            // update bundle
            $existing->setName($bundle->getName() ?? $existing->getName());
            $existing->setConfig($bundle->getConfig() !== null ? Parser::encode($bundle->getConfig()) : $existing->getConfig());
            $this->bundleTable->update($existing);

            $this->bundleTable->commit();
        } catch (\Throwable $e) {
            $this->bundleTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new UpdatedEvent($bundle, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $bundleId, UserContext $context): int
    {
        $existing = $this->bundleTable->findOneByIdentifier($context->getTenantId(), $bundleId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find bundle');
        }

        if ($existing->getStatus() == Table\Bundle::STATUS_DELETED) {
            throw new StatusCode\GoneException('Bundle was deleted');
        }

        $existing->setStatus(Table\Bundle::STATUS_DELETED);
        $this->bundleTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
