<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Cronjob
{
    private Table\Cronjob $cronjobTable;
    private Cronjob\Validator $validator;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Cronjob $cronjobTable, Cronjob\Validator $validator, EventDispatcherInterface $eventDispatcher)
    {
        $this->cronjobTable = $cronjobTable;
        $this->validator = $validator;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, CronjobCreate $cronjob, UserContext $context): int
    {
        $this->validator->assert($cronjob);

        // create cronjob
        try {
            $this->cronjobTable->beginTransaction();

            $row = new Table\Generated\CronjobRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($categoryId);
            $row->setStatus(Table\Cronjob::STATUS_ACTIVE);
            $row->setName($cronjob->getName());
            $row->setCron($cronjob->getCron());
            $row->setAction($cronjob->getAction());
            $row->setMetadata($cronjob->getMetadata() !== null ? json_encode($cronjob->getMetadata()) : null);
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
        $existing = $this->cronjobTable->findOneByIdentifier($context->getTenantId(), $cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $this->validator->assert($cronjob, $existing);

        $existing->setName($cronjob->getName() ?? $existing->getName());
        $existing->setCron($cronjob->getCron() ?? $existing->getCron());
        $existing->setAction($cronjob->getAction() ?? $existing->getAction());
        $existing->setMetadata($cronjob->getMetadata() !== null ? json_encode($cronjob->getMetadata()) : $existing->getMetadata());
        $this->cronjobTable->update($existing);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjob, $existing, $context));

        return $existing->getId();
    }

    public function delete(string $cronjobId, UserContext $context): int
    {
        $existing = $this->cronjobTable->findOneByIdentifier($context->getTenantId(), $cronjobId);
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
