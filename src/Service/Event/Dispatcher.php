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

namespace Fusio\Impl\Service\Event;

use Fusio\Engine\DispatcherInterface;
use Fusio\Impl\Messenger\TriggerEvent;
use Fusio\Impl\Service\System\FrameworkConfig;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Dispatcher
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Dispatcher implements DispatcherInterface
{
    private MessageBusInterface $messageBus;
    private FrameworkConfig $frameworkConfig;

    public function __construct(MessageBusInterface $messageBus, FrameworkConfig $frameworkConfig)
    {
        $this->messageBus = $messageBus;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function dispatch(string $eventName, mixed $payload): void
    {
        $this->messageBus->dispatch(new TriggerEvent($this->frameworkConfig->getTenantId(), $eventName, $payload));
    }
}
