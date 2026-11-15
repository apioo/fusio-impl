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

namespace Fusio\Impl\Service\Agent\A2A;

use Fusio\Impl\Framework\Loader\ContextFactory;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Json\Rpc\Exception\MethodNotFoundException;
use PSX\Json\Rpc\Context as RpcContext;
use PSX\Record\Record;
use stdClass;

/**
 * Server
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Server
{
    public function __construct(
        private ContextFactory $contextFactory,
    ) {
    }

    /**
     * @param array<string, mixed>|stdClass|null $params
     */
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

        // @TODO implement agent2agent JSON rpc methods

        return match ($method) {
            'SendMessage' => null,
            'GetTask' => null,
            'CancelTask' => null,
            'ListTasks' => null,
            default => throw new MethodNotFoundException('Method not found'),
        };
    }
}
