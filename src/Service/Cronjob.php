<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Cronjob\CreatedEvent;
use Fusio\Impl\Event\Cronjob\DeletedEvent;
use Fusio\Impl\Event\Cronjob\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\CronjobCreate;
use Fusio\Model\Backend\CronjobUpdate;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Cronjob
{
    private Table\Cronjob $cronjobTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Cronjob $cronjobTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->cronjobTable    = $cronjobTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, CronjobCreate $cronjob, UserContext $context): int
    {
        Cronjob\Validator::assertCron($cronjob->getCron());

        // check whether cronjob exists
        if ($this->exists($cronjob->getName())) {
            throw new StatusCode\BadRequestException('Cronjob already exists');
        }

        // create cronjob
        try {
            $this->cronjobTable->beginTransaction();

            $record = new Table\Generated\CronjobRow([
                Table\Generated\CronjobTable::COLUMN_CATEGORY_ID => $categoryId,
                Table\Generated\CronjobTable::COLUMN_STATUS => Table\Cronjob::STATUS_ACTIVE,
                Table\Generated\CronjobTable::COLUMN_NAME => $cronjob->getName(),
                Table\Generated\CronjobTable::COLUMN_CRON => $cronjob->getCron(),
                Table\Generated\CronjobTable::COLUMN_ACTION => $cronjob->getAction(),
                Table\Generated\CronjobTable::COLUMN_METADATA => $cronjob->getMetadata() !== null ? json_encode($cronjob->getMetadata()) : null,
            ]);

            $this->cronjobTable->create($record);

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

    public function update(int $cronjobId, CronjobUpdate $cronjob, UserContext $context): int
    {
        Cronjob\Validator::assertCron($cronjob->getCron());

        $existing = $this->cronjobTable->find($cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = new Table\Generated\CronjobRow([
            Table\Generated\CronjobTable::COLUMN_ID => $existing->getId(),
            Table\Generated\CronjobTable::COLUMN_NAME => $cronjob->getName(),
            Table\Generated\CronjobTable::COLUMN_CRON => $cronjob->getCron(),
            Table\Generated\CronjobTable::COLUMN_ACTION => $cronjob->getAction(),
            Table\Generated\CronjobTable::COLUMN_METADATA => $cronjob->getMetadata() !== null ? json_encode($cronjob->getMetadata()) : null,
        ]);

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjob, $existing, $context));

        return $cronjobId;
    }

    public function delete(int $cronjobId, UserContext $context): int
    {
        $existing = $this->cronjobTable->find($cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = new Table\Generated\CronjobRow([
            Table\Generated\CronjobTable::COLUMN_ID => $existing->getId(),
            Table\Generated\CronjobTable::COLUMN_STATUS => Table\Cronjob::STATUS_DELETED,
        ]);

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $cronjobId;
    }

    /**
     * Executes a specific cronjob
     */
    public function execute(string|int $cronjobId)
    {

    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\CronjobTable::COLUMN_STATUS, Table\Cronjob::STATUS_ACTIVE);
        $condition->equals(Table\Generated\CronjobTable::COLUMN_NAME, $name);

        $cronjob = $this->cronjobTable->findOneBy($condition);

        if ($cronjob instanceof Table\Generated\CronjobRow) {
            return $cronjob->getId();
        } else {
            return false;
        }
    }
}
