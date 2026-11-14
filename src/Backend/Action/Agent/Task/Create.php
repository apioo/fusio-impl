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

namespace Fusio\Impl\Backend\Action\Agent\Task;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Agent\Task;
use Fusio\Impl\Service\TypeSystem\KindBuilder;
use Fusio\Model\Backend\AgentTaskCreate;
use Fusio\Model\Common\Message;
use PSX\Http\Environment\HttpResponse;

/**
 * Create
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Create implements ActionInterface
{
    public function __construct(private Task $taskService)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof AgentTaskCreate);

        $connection = $request->get('connection');

        $id = $this->taskService->create(
            $request->get('agent_id'),
            $body,
            !empty($connection) ? (string) $connection : null,
            $context
        );

        return new HttpResponse(202, [], [
            'kind' => KindBuilder::build(Message::class),
            'success' => true,
            'message' => 'Agent task successfully created',
            'id' => '' . $id,
        ]);
    }
}
