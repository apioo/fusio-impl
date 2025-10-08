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

namespace Fusio\Impl\MessengerHandler;

use Fusio\Engine\Context;
use Fusio\Engine\Processor;
use Fusio\Engine\Request;
use Fusio\Impl\Messenger\InvokeCronjob;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Invokes an action which is associated with a cronjob
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
readonly class InvokeCronjobHandler
{
    public function __construct(
        private Processor $processor,
        private Table\Cronjob $cronjobTable,
        private Table\Cronjob\Error $errorTable
    ) {
    }

    public function __invoke(InvokeCronjob $event): void
    {
        $cronjob = $event->getCronjob();

        try {
            $action = $cronjob->getAction();
            if (empty($action)) {
                return;
            }

            $request = new Request([], null, new Request\CronjobRequestContext());
            $context = new Context\AnonymousContext($event->getTenantId());

            $this->processor->execute($action, $request, $context, false);

            $exitCode = Table\Cronjob::CODE_SUCCESS;
        } catch (\Throwable $e) {
            $row = new Table\Generated\CronjobErrorRow();
            $row->setCronjobId($cronjob->getId());
            $row->setMessage($e->getMessage());
            $row->setTrace($e->getTraceAsString());
            $row->setFile($e->getFile());
            $row->setLine($e->getLine());
            $row->setInsertDate(LocalDateTime::now());
            $this->errorTable->create($row);

            $exitCode = Table\Cronjob::CODE_ERROR;
        }

        // set execute date
        $cronjob->setExecuteDate(LocalDateTime::now());
        $cronjob->setExitCode($exitCode);
        $this->cronjobTable->update($cronjob);
    }
}
