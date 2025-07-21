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

namespace Fusio\Impl\Service\Mcp;

use Fusio\Impl\Action\Scheme as ActionScheme;
use Fusio\Impl\Framework\Schema\Scheme as SchemaScheme;
use Fusio\Impl\Repository\UserDatabase;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\SchemaRow;
use Mcp\Types\ListResourcesResult;
use Mcp\Types\PaginatedRequestParams;
use Mcp\Types\ReadResourceRequestParams;
use Mcp\Types\ReadResourceResult;
use Mcp\Types\Resource;
use Mcp\Types\TextResourceContents;
use PSX\Schema\Exception\InvalidSchemaException;
use PSX\Schema\Generator\JsonSchema;
use PSX\Schema\SchemaManager;
use PSX\Schema\SchemaSource;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Resources
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Resources
{
    public function __construct(
        private Table\Operation $operationTable,
        private Table\Schema $schemaTable,
        private Table\Action $actionTable,
        private FrameworkConfig $frameworkConfig,
        private ActiveUser $activeUser,
        private UserDatabase $userRepository,
        private SchemaManager $schemaManager,
    ) {
    }

    public function list(PaginatedRequestParams $params): ListResourcesResult
    {
        $cursor = $params->cursor ?? null;

        $userId = $this->activeUser->getUserId();
        if (!empty($userId)) {
            $user = $this->userRepository->get($userId) ?? throw new \RuntimeException('Provided an invalid active user');
            $categoryId = $user->getCategoryId();
        } else {
            $categoryId = null;
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        if ($categoryId !== null) {
            $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $categoryId);
        }
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, 1);
        $condition->like(Table\Generated\OperationTable::COLUMN_ACTION, 'action://%');

        $count = 32;
        $startIndex = empty($cursor) ? 0 : ((int) base64_decode($cursor));
        $nextCursor = base64_encode('' . ($startIndex + $count));

        $resources = [];
        $operations = $this->operationTable->findAll($condition, $startIndex, $count, Table\Generated\OperationColumn::ID, OrderBy::DESC);
        foreach ($operations as $operation) {
            $incoming = $operation->getIncoming();
            if (!empty($incoming)) {
                $resource = $this->resolveSchemaResource($incoming, $categoryId);
                if ($resource !== null) {
                    $resources[] = $resource;
                }
            }

            $outgoing = $operation->getOutgoing();
            if (!empty($outgoing)) {
                $resource = $this->resolveSchemaResource($outgoing, $categoryId);
                if ($resource !== null) {
                    $resources[] = $resource;
                }
            }

            $action = $operation->getAction();
            if (!empty($action)) {
                $resource = $this->resolveActionResource($action, $categoryId);
                if ($resource !== null) {
                    $resources[] = $resource;
                }
            }
        }

        if (count($resources) === 0) {
            $nextCursor = null;
        }

        return new ListResourcesResult($resources, $nextCursor);
    }

    public function get(ReadResourceRequestParams $params): ReadResourceResult
    {
        $uri = $params->uri;

        $userId = $this->activeUser->getUserId();
        if (!empty($userId)) {
            $user = $this->userRepository->get($userId) ?? throw new \RuntimeException('Provided an invalid active user');
            $categoryId = $user->getCategoryId();
        } else {
            $categoryId = null;
        }

        if (str_starts_with($uri, 'schema://')) {
            $result = $this->resolveSchemaResourceResult(substr($uri, 7));
            if ($result instanceof ReadResourceResult) {
                return $result;
            }
        } elseif (str_starts_with($uri, 'action://')) {
            $result = $this->resolveActionResourceResult(substr($uri, 7), $categoryId);
            if ($result instanceof ReadResourceResult) {
                return $result;
            }
        }

        throw new \InvalidArgumentException('Could not resolve resource: ' . $uri);
    }

    private function resolveSchemaResource(string $schemaUri, ?int $categoryId): ?Resource
    {
        [$scheme, $value] = SchemaScheme::split($schemaUri);

        if ($scheme === SchemaScheme::SCHEMA) {
            $schemaRow = $this->schemaTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), $categoryId, $value);
            if (!$schemaRow instanceof SchemaRow) {
                return null;
            }

            $name = $schemaRow->getName();
        } else {
            return null;
        }

        return new Resource(
            name: $name,
            uri: $schemaUri,
            mimeType: 'application/json'
        );
    }

    private function resolveSchemaResourceResult(string $schemaUri): ?ReadResourceResult
    {
        try {
            $schema = $this->schemaManager->getSchema(SchemaSource::fromString($schemaUri));

            $text = (string) (new JsonSchema(inlineDefinitions: true))->generate($schema);
        } catch (InvalidSchemaException $e) {
            return null;
        }

        return new ReadResourceResult([
            new TextResourceContents(
                text: $text,
                uri: $schemaUri,
                mimeType: 'application/json'
            )
        ]);
    }

    private function resolveActionResource(string $actionUri, ?int $categoryId): ?Resource
    {
        [$scheme, $value] = ActionScheme::split($actionUri);

        if ($scheme === ActionScheme::ACTION) {
            $actionRow = $this->actionTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), $categoryId, $value);
            if (!$actionRow instanceof Table\Generated\ActionRow) {
                return null;
            }

            $name = $actionRow->getName();
            $mimeType = 'application/json';
        } else {
            return null;
        }

        return new Resource(
            name: $name,
            uri: $actionUri,
            mimeType: $mimeType
        );
    }

    private function resolveActionResourceResult(string $actionUri, ?int $categoryId): ?ReadResourceResult
    {
        [$scheme, $value] = ActionScheme::split($actionUri);

        if ($scheme === ActionScheme::ACTION) {
            $actionRow = $this->actionTable->findOneByTenantAndName($this->frameworkConfig->getTenantId(), $categoryId, $value);
            if (!$actionRow instanceof Table\Generated\ActionRow) {
                return null;
            }

            $text = $actionRow->getConfig();
            $mimeType = 'application/json';
        } else {
            return null;
        }

        if (empty($text)) {
            return null;
        }

        return new ReadResourceResult([
            new TextResourceContents(
                text: $text,
                uri: $actionUri,
                mimeType: $mimeType
            )
        ]);
    }
}
