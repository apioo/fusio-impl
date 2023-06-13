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
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
