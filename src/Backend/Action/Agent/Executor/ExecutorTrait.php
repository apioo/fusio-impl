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

namespace Fusio\Impl\Backend\Action\Agent\Executor;

use Fusio\Engine\RequestInterface;

/**
 * Reads the arguments which are passed by the agent task executor to the result action of an agent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
trait ExecutorTrait
{
    private function getTaskId(RequestInterface $request): ?int
    {
        $taskId = $request->get('taskId');

        return !empty($taskId) ? (int) $taskId : null;
    }

    private function getAgentId(RequestInterface $request): ?int
    {
        $agentId = $request->get('agentId');

        return !empty($agentId) ? (int) $agentId : null;
    }

    private function getConnectionId(RequestInterface $request): ?int
    {
        $connectionId = $request->get('connectionId');

        return !empty($connectionId) ? (int) $connectionId : null;
    }
}
