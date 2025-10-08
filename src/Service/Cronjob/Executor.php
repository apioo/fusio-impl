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

namespace Fusio\Impl\Service\Cronjob;

use Cron\CronExpression;
use Fusio\Impl\Messenger\InvokeCronjob;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Executor
{
    public function __construct(
        private Table\Cronjob $cronjobTable,
        private MessageBusInterface $messageBus,
        private FrameworkConfig $frameworkConfig,
    ) {
    }

    public function execute(): void
    {
        $result = $this->getCronjobsToExecute();
        foreach ($result as $cronjob) {
            $this->messageBus->dispatch(new InvokeCronjob($this->frameworkConfig->getTenantId(), $cronjob));
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
        $result = $this->cronjobTable->findByTenantAndStatus($this->frameworkConfig->getTenantId(), Table\Cronjob::STATUS_ACTIVE);
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
}
