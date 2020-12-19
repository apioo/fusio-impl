<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Backend\Model\Action_Execute_Request;
use Fusio\Impl\Backend\Model\Cronjob_Create;
use Fusio\Impl\Backend\Model\Cronjob_Update;
use Fusio\Impl\Event\Cronjob\CreatedEvent;
use Fusio\Impl\Event\Cronjob\DeletedEvent;
use Fusio\Impl\Event\Cronjob\UpdatedEvent;
use Fusio\Impl\Event\CronjobEvents;
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
 * @link    http://fusio-project.org
 */
class Cronjob
{
    /**
     * @var \Fusio\Impl\Table\Cronjob
     */
    private $cronjobTable;

    /**
     * @var \Fusio\Impl\Table\Cronjob\Error
     */
    private $errorTable;

    /**
     * @var \Fusio\Impl\Service\Action\Executor
     */
    private $executorService;

    /**
     * @var string
     */
    private $cronFile;

    /**
     * @var string
     */
    private $cronExec;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Cronjob $cronjobTable
     * @param \Fusio\Impl\Table\Cronjob\Error $errorTable
     * @param \Fusio\Impl\Service\Action\Executor $executorService
     * @param string $cronFile
     * @param string $cronExec
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Cronjob $cronjobTable, Table\Cronjob\Error $errorTable, Executor $executorService, $cronFile, $cronExec, EventDispatcherInterface $eventDispatcher)
    {
        $this->cronjobTable    = $cronjobTable;
        $this->errorTable      = $errorTable;
        $this->executorService = $executorService;
        $this->cronFile        = $cronFile;
        $this->cronExec        = $cronExec;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(Cronjob_Create $cronjob, UserContext $context)
    {
        Cronjob\Validator::assertCron($cronjob->getCron());

        // check whether cronjob exists
        if ($this->exists($cronjob->getName())) {
            throw new StatusCode\BadRequestException('Cronjob already exists');
        }

        // create cronjob
        $record = [
            'status' => Table\Cronjob::STATUS_ACTIVE,
            'name'   => $cronjob->getName(),
            'cron'   => $cronjob->getCron(),
            'action' => $cronjob->getAction(),
        ];

        $this->cronjobTable->create($record);

        $cronjobId = $this->cronjobTable->getLastInsertId();

        $this->eventDispatcher->dispatch(new CreatedEvent($cronjob, $context));

        $this->writeCronFile();

        return $cronjobId;
    }

    public function update(int $cronjobId, Cronjob_Update $cronjob, UserContext $context)
    {
        Cronjob\Validator::assertCron($cronjob->getCron());

        $existing = $this->cronjobTable->get($cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = [
            'id'     => $existing['id'],
            'name'   => $cronjob->getName(),
            'cron'   => $cronjob->getCron(),
            'action' => $cronjob->getAction(),
        ];

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjob, $existing, $context));

        $this->writeCronFile();
    }

    public function delete(int $cronjobId, UserContext $context)
    {
        $existing = $this->cronjobTable->get($cronjobId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Cronjob::STATUS_DELETED,
        ];

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        $this->writeCronFile();
    }

    /**
     * Executes a specific cronjob
     * 
     * @param string|integer $cronjobId
     */
    public function execute($cronjobId)
    {
        if (is_numeric($cronjobId)) {
            $existing = $this->cronjobTable->get($cronjobId);
        } else {
            $condition = new Condition();
            $condition->equals('name', $cronjobId);
            $existing = $this->cronjobTable->getOneBy($condition);
        }

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($existing['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $exitCode = null;
        try {
            $execute = new Action_Execute_Request();
            $execute->setMethod('GET');

            $this->executorService->execute($existing['action'], $execute);

            $exitCode = Table\Cronjob::CODE_SUCCESS;
        } catch (\Throwable $e) {
            $this->errorTable->create([
                'cronjob_id' => $existing['id'],
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            $exitCode = Table\Cronjob::CODE_ERROR;
        }

        // set execute date
        $record = [
            'id' => $existing['id'],
            'execute_date' => new \DateTime(),
            'exit_code' => $exitCode,
        ];

        $this->cronjobTable->update($record);
    }

    public function exists(string $name)
    {
        $condition  = new Condition();
        $condition->equals('status', Table\Cronjob::STATUS_ACTIVE);
        $condition->equals('name', $name);

        $cronjob = $this->cronjobTable->getOneBy($condition);

        if (!empty($cronjob)) {
            return $cronjob['id'];
        } else {
            return false;
        }
    }

    /**
     * Writes the cron file
     * 
     * @return bool|int
     */
    private function writeCronFile()
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

    /**
     * @return string
     */
    private function generateCron()
    {
        $condition = new Condition();
        $condition->equals('status', Table\Cronjob::STATUS_ACTIVE);

        $result = $this->cronjobTable->getAll(0, 1024, null, null, $condition);
        $lines  = [];

        foreach ($result as $row) {
            $lines[] = $row['cron'] . ' ' . $this->cronExec . ' cronjob:execute ' . $row['id'];
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
