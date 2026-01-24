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

namespace Fusio\Impl\Backend\Action\Connection\Agent;

use Fusio\Engine\Connector;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\BadRequestException;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Get extends AgentAbstract
{
    public function __construct(private Table\AgentChat $agentChatTable, Connector $connector, FrameworkConfig $frameworkConfig)
    {
        parent::__construct($connector, $frameworkConfig);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $this->assertConnectionEnabled();

        $connectionId = (int) $request->get('connection_id');
        if (empty($connectionId)) {
            throw new BadRequestException('Provided no connection');
        }

        return new HttpResponse(200, [], [
            'messages' => $this->agentChatTable->findMessages($context->getUser()->getId(), $connectionId),
        ]);
    }
}
