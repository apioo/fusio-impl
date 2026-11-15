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

namespace Fusio\Impl\Service\Agent\Executor;

use Fusio\Engine\ContextInterface;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Resolves the connection which an executor should use, if a task was started without a connection we use the
 * "System" connection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class ConnectionResolver
{
    public const DEFAULT_CONNECTION = 'System';

    public function __construct(private Table\Connection $connectionTable)
    {
    }

    public function resolve(?int $connectionId, ContextInterface $context): int
    {
        if (!empty($connectionId)) {
            return $connectionId;
        }

        $row = $this->connectionTable->findOneByTenantAndName($context->getTenantId(), $context->getUser()->getCategoryId(), self::DEFAULT_CONNECTION);
        if (!$row instanceof Table\Generated\ConnectionRow) {
            throw new StatusCode\BadRequestException('Provided no connection and the default "' . self::DEFAULT_CONNECTION . '" connection does not exist');
        }

        return $row->getId();
    }

    public function getName(?int $connectionId, ContextInterface $context): ?string
    {
        if (empty($connectionId)) {
            return self::DEFAULT_CONNECTION;
        }

        $row = $this->connectionTable->findOneByTenantAndId($context->getTenantId(), $context->getUser()->getCategoryId(), $connectionId);

        return $row?->getName();
    }
}
