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

use Fusio\Impl\Messenger\InvokeAgent;
use Fusio\Impl\Service\Agent\Sender;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Model;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * InvokeAgentHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
readonly class InvokeAgentHandler
{
    public function __construct(private Sender $sender, private Table\Agent $agentTable, private FrameworkConfig $frameworkConfig)
    {
    }

    public function __invoke(InvokeAgent $agent): void
    {
        $row = $this->agentTable->findOneByTenantAndId($this->frameworkConfig->getTenantId(), null, $agent->getAgentId());
        if (!$row instanceof Table\Generated\AgentRow) {
            throw new InvalidRequestException('Provided an invalid agent id');
        }

        $output = $this->sender->send($row->getId(), $agent->getInput(), $context);

        // @TODO handle output
    }
}
