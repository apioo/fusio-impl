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
use Fusio\Model\Backend\Action_Execute_Request;
use Fusio\Model\Backend\Cronjob_Create;
use Fusio\Model\Backend\Cronjob_Update;
use Fusio\Impl\Event\Cronjob\CreatedEvent;
use Fusio\Impl\Event\Cronjob\DeletedEvent;
use Fusio\Impl\Event\Cronjob\UpdatedEvent;
use Fusio\Impl\Service\Action\Executor;
use Fusio\Impl\Table;
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
    private Table\Cronjob\Error $errorTable;
    private Executor $executorService;
    private ?string $cronFile;
    private ?string $cronExec;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Cronjob $cronjobTable, Table\Cronjob\Error $errorTable, Executor $executorService, ?string $cronFile, ?string $cronExec, EventDispatcherInterface $eventDispatcher)
    {
        $this->cronjobTable    = $cronjobTable;
        $this->errorTable      = $errorTable;
        $this->executorService = $executorService;
        $this->cronFile        = $cronFile;
        $this->cronExec        = $cronExec;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(int $categoryId, Cronjob_Create $cronjob, UserContext $context): int
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

        $this->flush();

        return $cronjobId;
    }

    public function update(int $cronjobId, Cronjob_Update $cronjob, UserContext $context): int
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
        ]);

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjob, $existing, $context));

        $this->flush();

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

        $this->flush();

        return $cronjobId;
    }

    /**
     * Executes a specific cronjob
     */
    public function execute(string|int $cronjobId)
    {
        if (is_numeric($cronjobId)) {
            $existing = $this->cronjobTable->find((int) $cronjobId);
        } else {
            $existing = $this->cronjobTable->findOneByName($cronjobId);
        }

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing->getStatus() == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        try {
            $execute = new Action_Execute_Request();
            $execute->setMethod('GET');

            $this->executorService->execute($existing->getAction(), $execute);

            $exitCode = Table\Cronjob::CODE_SUCCESS;
        } catch (\Throwable $e) {
            $this->errorTable->create(new Table\Generated\CronjobErrorRow([
                Table\Generated\CronjobErrorTable::COLUMN_CRONJOB_ID => $existing->getId(),
                Table\Generated\CronjobErrorTable::COLUMN_MESSAGE => $e->getMessage(),
                Table\Generated\CronjobErrorTable::COLUMN_TRACE => $e->getTraceAsString(),
                Table\Generated\CronjobErrorTable::COLUMN_FILE => $e->getFile(),
                Table\Generated\CronjobErrorTable::COLUMN_LINE => $e->getLine(),
            ]));

            $exitCode = Table\Cronjob::CODE_ERROR;
        }

        // set execute date
        $record = new Table\Generated\CronjobRow([
            Table\Generated\CronjobTable::COLUMN_ID => $existing->getId(),
            Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE => new \DateTime(),
            Table\Generated\CronjobTable::COLUMN_EXIT_CODE => $exitCode,
        ]);

        $this->cronjobTable->update($record);
    }

    public function exists(string $name): int|false
    {
        $condition  = new Condition();
        $condition->equals(Table\Generated\CronjobTable::COLUMN_STATUS, Table\Cronjob::STATUS_ACTIVE);
        $condition->equals(Table\Generated\CronjobTable::COLUMN_NAME, $name);

        $cronjob = $this->cronjobTable->findOneBy($condition);

        if ($cronjob instanceof Table\Generated\CronjobRow) {
            return $cronjob->getId() ?? false;
        } else {
            return false;
        }
    }

    /**
     * Writes the cron file
     */
    public function flush(): int|false
    {
        if (empty($this->cronFile)) {
            return false;
        }

        if (!is_file($this->cronFile)) {
            return false;
        }

        if (!is_writable($this->cronFile)) {
            return false;
        }

        return file_put_contents($this->cronFile, $this->generateCron());
    }

    private function generateCron(): string
    {
        $condition = new Condition();
        $condition->equals(Table\Generated\CronjobTable::COLUMN_STATUS, Table\Cronjob::STATUS_ACTIVE);

        $result = $this->cronjobTable->findAll($condition, 0, 1024);
        $lines  = [];

        foreach ($result as $row) {
            $lines[] = implode(' ', [
                $row->getCron(),
                sprintf($this->cronExec, $row->getId()),
            ]);
        }

        $cron = '# Generated by Fusio on ' . date('Y-m-d H:i:s') . "\n";
        $cron.= '# Do not edit this file manually since Fusio will overwrite those' . "\n";
        $cron.= '# entries on generation.' . "\n";
        $cron.= "\n";
        $cron.= implode("\n", $lines) . "\n";
        $cron.= "\n";

        return $cron;
    }
}
