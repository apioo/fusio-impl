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

namespace Fusio\Impl\Service\Cronjob;

use Cron\CronExpression;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\ActionExecuteRequest;
use PSX\DateTime\LocalDateTime;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Executor
{
    private Table\Cronjob $cronjobTable;
    private Table\Cronjob\Error $errorTable;
    private Service\Action\Executor $executorService;

    public function __construct(Table\Cronjob $cronjobTable, Table\Cronjob\Error $errorTable, Service\Action\Executor $executorService)
    {
        $this->cronjobTable = $cronjobTable;
        $this->errorTable = $errorTable;
        $this->executorService = $executorService;
    }

    public function execute(): void
    {
        $result = $this->getCronjobsToExecute();
        foreach ($result as $cronjob) {
            $this->executeCronjob($cronjob);
        }
    }

    public function executeDaemon(): void
    {
        while (true) {
            $this->execute();
            sleep(60);
        }
    }

    private function getCronjobsToExecute(): array
    {
        $execute = [];
        $result = $this->cronjobTable->findByStatus(Table\Cronjob::STATUS_ACTIVE);
        foreach ($result as $cronjob) {
            if (!$this->shouldExecute($cronjob)) {
                continue;
            }

            $execute[] = $cronjob;
        }

        return $execute;
    }

    private function shouldExecute(Table\Generated\CronjobRow $cronjob): bool
    {
        return (new CronExpression($cronjob->getCron()))->isDue();
    }

    private function executeCronjob(Table\Generated\CronjobRow $cronjob)
    {
        try {
            $execute = new ActionExecuteRequest();
            $execute->setMethod('GET');

            $this->executorService->execute($cronjob->getAction() ?? '', $execute);

            $exitCode = Table\Cronjob::CODE_SUCCESS;
        } catch (\Throwable $e) {
            $row = new Table\Generated\CronjobErrorRow();
            $row->setCronjobId($cronjob->getId());
            $row->setMessage($e->getMessage());
            $row->setTrace($e->getTraceAsString());
            $row->setFile($e->getFile());
            $row->setLine($e->getLine());
            $this->errorTable->create($row);

            $exitCode = Table\Cronjob::CODE_ERROR;
        }

        // set execute date
        $cronjob->setExecuteDate(LocalDateTime::now());
        $cronjob->setExitCode($exitCode);
        $this->cronjobTable->update($cronjob);
    }
}
