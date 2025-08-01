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
use Fusio\Impl\Event\Cronjob\CreatedEvent;
use Fusio\Impl\Event\Cronjob\DeletedEvent;
use Fusio\Impl\Event\Cronjob\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\CronjobCreate;
use Fusio\Model\Backend\CronjobUpdate;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Cronjob
{
    public function __construct(
        private Table\Cronjob $cronjobTable,
        private Cronjob\Validator $validator,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function create(CronjobCreate $cronjob, UserContext $context): int
    {
        $this->validator->assert($cronjob, $context->getTenantId());

        // create cronjob
        try {
            $this->cronjobTable->beginTransaction();

            $row = new Table\Generated\CronjobRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setStatus(Table\Cronjob::STATUS_ACTIVE);
            $row->setName($cronjob->getName());
            $row->setCron($cronjob->getCron());
            $row->setAction($cronjob->getAction());
            $row->setMetadata($cronjob->getMetadata() !== null ? Parser::encode($cronjob->getMetadata()) : null);
            $this->cronjobTable->create($row);

            $cronjobId = $this->cronjobTable->getLastInsertId();
            $cronjob->setId($cronjobId);

            $this->cronjobTable->commit();
        } catch (\Throwable $e) {
            $this->cronjobTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($cronjob, $context));

        return $cronjobId;
    }

    public function update(string $cronjobId, CronjobUpdate $cronjob, UserContext $context): int
    {
        $existing = $this->cronjobTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $this->validator->assert($cronjob, $context->getTenantId(), $existing);

        $existing->setName($cronjob->getName() ?? $existing->getName());
        $existing->setCron($cronjob->getCron() ?? $existing->getCron());
        $existing->setAction($cronjob->getAction() ?? $existing->getAction());
        $existing->setMetadata($cronjob->getMetadata() !== null ? Parser::encode($cronjob->getMetadata()) : $existing->getMetadata());
        $this->cronjobTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjob, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $cronjobId, UserContext $context): int
    {
        $existing = $this->cronjobTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), $cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $existing->setStatus(Table\Cronjob::STATUS_DELETED);
        $this->cronjobTable->update($existing);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $existing->getId();
    }
}
