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

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Agent\Executor\SeedExecutor;
use Fusio\Impl\Service\Agent\Task;
use Fusio\Model\Agent\Seed as SeedModel;
use PSX\Schema\ObjectMapperInterface;
use PSX\Schema\SchemaSource;

/**
 * Result action of the seed agent, inserts all generated rows on the connection of the task (default "System")
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Seed implements ActionInterface
{
    use ExecutorTrait;

    public function __construct(private SeedExecutor $executor, private Task $taskService, private ObjectMapperInterface $objectMapper)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $model = $this->objectMapper->read($request->getPayload(), SchemaSource::fromClass(SeedModel::class));
        $logger = $this->taskService->newStepLogger($this->getTaskId($request));

        return $this->executor->execute($model, $this->getConnectionId($request), $context, $logger);
    }
}
