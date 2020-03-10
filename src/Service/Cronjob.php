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
    protected $cronjobTable;

    /**
     * @var \Fusio\Impl\Table\Cronjob\Error
     */
    protected $errorTable;

    /**
     * @var \Fusio\Impl\Service\Action\Executor
     */
    protected $executorService;

    /**
     * @var string
     */
    protected $cronFile;

    /**
     * @var string
     */
    protected $cronExec;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

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

    public function create($name, $cron, $action, UserContext $context)
    {
        // check whether cronjob exists
        if ($this->exists($name)) {
            throw new StatusCode\BadRequestException('Cronjob already exists');
        }

        // create cronjob
        $record = [
            'status' => Table\Cronjob::STATUS_ACTIVE,
            'name'   => $name,
            'cron'   => $cron,
            'action' => $action,
        ];

        $this->cronjobTable->create($record);

        $cronjobId = $this->cronjobTable->getLastInsertId();

        $this->eventDispatcher->dispatch(new CreatedEvent($cronjobId, $record, $context), CronjobEvents::CREATE);

        $this->writeCronFile();

        return $cronjobId;
    }

    public function update($cronjobId, $name, $cron, $action, UserContext $context)
    {
        $cronjob = $this->cronjobTable->get($cronjobId);

        if (empty($cronjob)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($cronjob['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = [
            'id'     => $cronjob['id'],
            'name'   => $name,
            'cron'   => $cron,
            'action' => $action,
        ];

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($cronjobId, $record, $cronjob, $context), CronjobEvents::UPDATE);

        $this->writeCronFile();
    }

    public function delete($cronjobId, UserContext $context)
    {
        $cronjob = $this->cronjobTable->get($cronjobId);

        if (empty($cronjob)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($cronjob['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $record = [
            'id'     => $cronjob['id'],
            'status' => Table\Cronjob::STATUS_DELETED,
        ];

        $this->cronjobTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($cronjobId, $cronjob, $context), CronjobEvents::DELETE);

        $this->writeCronFile();
    }

    /**
     * Executes a specific cronjob
     * 
     * @param integer $cronjobId
     */
    public function execute($cronjobId)
    {
        $cronjob = $this->cronjobTable->get($cronjobId);

        if (empty($cronjob)) {
            throw new StatusCode\NotFoundException('Could not find cronjob');
        }

        if ($cronjob['status'] == Table\Cronjob::STATUS_DELETED) {
            throw new StatusCode\GoneException('Cronjob was deleted');
        }

        $exitCode = null;
        try {
            $this->executorService->execute(
                $cronjob['action'],
                'GET',
                null,
                null,
                null,
                null
            );

            $exitCode = Table\Cronjob::CODE_SUCCESS;
        } catch (\Throwable $e) {
            $this->errorTable->create([
                'cronjob_id' => $cronjob['id'],
                'message'    => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
                'file'       => $e->getFile(),
                'line'       => $e->getLine(),
            ]);

            $exitCode = Table\Cronjob::CODE_ERROR;
        }

        // set execute date
        $record = [
            'id' => $cronjob['id'],
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
