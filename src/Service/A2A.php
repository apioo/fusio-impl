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

namespace Fusio\Impl\Service;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Service\A2A\CancelTask;
use Fusio\Impl\Service\A2A\GetTask;
use Fusio\Impl\Service\A2A\ListTasks;
use Fusio\Impl\Service\A2A\SendMessage;
use Fusio\Model;
use PSX\Json\Rpc\Context as RpcContext;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Record\Record;
use stdClass;

/**
 * A2A
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class A2A
{
    public function __construct(
        private SendMessage $sendMessage,
        private GetTask $getTask,
        private CancelTask $cancelTask,
        private ListTasks $listTasks,
        private ContextFactory $contextFactory,
    ) {
    }

    public function __invoke(string $method, array|stdClass|null $params, RpcContext $rpcContext): mixed
    {
        if (is_array($params)) {
            throw new InvalidRequestException('Params as array (by-position) are not supported, please use params as object (by-name)');
        }

        if ($params instanceof stdClass) {
            $arguments = Record::fromObject($params);
        } else {
            $arguments = new Record();
        }

        $context = $this->contextFactory->getActive();

        return match ($method) {
            'SendMessage' => $this->sendMessage->invoke($arguments, $context),
            'GetTask' => $this->getTask->invoke($arguments, $context),
            'CancelTask' => $this->cancelTask->invoke($arguments, $context),
            'ListTasks' => $this->listTasks->invoke($arguments, $context),
            default => throw new MethodNotFoundException('Method not found'),
        };
    }
}
