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
use Fusio\Impl\Messenger\TriggerEvent;
use Fusio\Impl\Table;
use PSX\Record\Record;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Invokes an action which is associated with this event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
readonly class InvokeTriggerHandler
{
    public function __construct(
        private Processor $processor,
        private Table\Trigger $triggerTable
    ) {
    }

    public function __invoke(TriggerEvent $event): void
    {
        $result = $this->triggerTable->findByTenantAndEvent($event->getTenantId(), $event->getEventName());
        foreach ($result as $row) {
            $action = $row->getAction();
            if (empty($action)) {
                return;
            }

            $payload = $event->getPayload();
            if (is_object($payload) || is_iterable($payload)) {
                $data = Record::from($payload);
            } else {
                $data = new Record();
            }

            $request = new Request([], $data, new Request\EventRequestContext($event->getEventName()));
            $context = $event->getContext() ?? new Context\AnonymousContext($event->getTenantId());

            $this->processor->execute($action, $request, $context, false);
        }
    }
}
