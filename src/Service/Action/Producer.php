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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Action\QueueInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Messenger\InvokeAction;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Producer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Producer implements QueueInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function push(string|int $actionId, RequestInterface $request, ContextInterface $context): void
    {
        $this->messageBus->dispatch(new InvokeAction($actionId, $request, $context));
    }
}
