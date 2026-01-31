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

namespace Fusio\Impl\Service\Agent;

use Fusio\Impl\Service\JsonRPC\RPCInvoker;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Record\Record;
use RuntimeException;
use Symfony\AI\Platform\Result\ToolCall;

/**
 * OperationTool
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class OperationTool
{
    public function __construct(
        private RPCInvoker $invoker,
        private Table\Operation $operationTable,
        private FrameworkConfig $frameworkConfig,
    ) {
    }

    public function invoke(ToolCall $toolCall): mixed
    {
        $operation = $this->operationTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), null, ToolName::toOperationId($toolCall->getName()));
        if (!$operation instanceof Table\Generated\OperationRow) {
            throw new RuntimeException('Provided an invalid operation name');
        }

        $arguments = Record::fromArray($toolCall->getArguments());

        $response = $this->invoker->invoke($operation, $arguments);

        return (string) $response->getBody();
    }
}
