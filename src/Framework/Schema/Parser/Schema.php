<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Framework\Schema\Parser;

use Doctrine\DBAL\Connection;
use PSX\Framework\Config\ConfigInterface;
use PSX\Schema\Exception\ParserException;
use PSX\Schema\Parser\ContextInterface;
use PSX\Schema\Parser\Popo;
use PSX\Schema\Parser\TypeSchema;
use PSX\Schema\ParserInterface;
use PSX\Schema\SchemaInterface;
use PSX\Schema\SchemaManagerInterface;
use PSX\Sql\Condition;
use Fusio\Impl\Table;

/**
 * Schema
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Schema implements ParserInterface
{
    private Connection $connection;
    private Popo $popo;
    private TypeSchema $typeSchema;
    private ConfigInterface $config;

    public function __construct(Connection $connection, SchemaManagerInterface $schemaManager, ConfigInterface $config)
    {
        $this->connection = $connection;
        $this->popo = new Popo();
        $this->typeSchema = new TypeSchema($schemaManager);
        $this->config = $config;
    }

    public function parse(string $schema, ?ContextInterface $context = null): SchemaInterface
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\SchemaTable::COLUMN_TENANT_ID, $this->getTenantId());

        if (is_numeric($schema)) {
            $condition->equals(Table\Generated\SchemaTable::COLUMN_ID, (int) $schema);
        } else {
            $condition->equals(Table\Generated\SchemaTable::COLUMN_NAME, ltrim($schema, '/'));
        }

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select([
                Table\Generated\SchemaTable::COLUMN_SOURCE,
            ])
            ->from('fusio_schema', 'schema')
            ->where($condition->getExpression($this->connection->getDatabasePlatform()))
            ->setParameters($condition->getValues());

        $source = $this->connection->fetchOne($queryBuilder->getSQL(), $queryBuilder->getParameters());
        if (empty($source)) {
            throw new ParserException('Could not find schema ' . $schema);
        }

        if (!str_contains($source, '{') && class_exists($source)) {
            return $this->popo->parse($source);
        } else {
            return $this->typeSchema->parse($source, $context);
        }
    }

    private function getTenantId(): ?string
    {
        $tenantId = $this->config->get('fusio_tenant_id');
        if (empty($tenantId)) {
            return null;
        }

        return $tenantId;
    }
}
