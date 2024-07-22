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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Page\UpdatedEvent;
use Fusio\Impl\Service\Page\SlugBuilder;
use Fusio\Impl\Table;
use Fusio\Model\Backend\PageUpdate;
use Fusio\Model\Backend\TestConfig;
use PSX\Sql\Condition;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\OrderBy;

/**
 * Test
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Test
{
    private Test\Runner $runner;
    private Table\Operation $operationTable;
    private Table\Test $testTable;

    public function __construct(Test\Runner $runner, Table\Operation $operationTable, Table\Test $testTable)
    {
        $this->runner = $runner;
        $this->operationTable = $operationTable;
        $this->testTable = $testTable;
    }

    public function refresh(UserContext $context): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $context->getCategoryId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, Table\Operation::STATUS_ACTIVE);
        $operations = $this->operationTable->findAll($condition, 0, 1024, Table\Generated\OperationTable::COLUMN_NAME, OrderBy::ASC);

        foreach ($operations as $operation) {
            $row = new Table\Generated\TestRow();
            $row->setTenantId($context->getTenantId());
            $row->setCategoryId($context->getCategoryId());
            $row->setOperationId($operation->getId());
            $row->setStatus(Table\Test::STATUS_PENDING);
            $this->testTable->create($row);
        }
    }

    public function run(UserContext $context): void
    {
        $token = $this->runner->authenticate($context);

        $condition = Condition::withAnd();
        $condition->notEquals(Table\Test::COLUMN_STATUS, Table\Test::STATUS_DISABLED);
        $tests = $this->testTable->findAll($condition, 0, 1024);
        foreach ($tests as $test) {
            $operation = $this->operationTable->findOneByIdentifier($context->getTenantId(), '' . $test->getOperationId());
            if (!$operation instanceof Table\Generated\OperationRow) {
                continue;
            }

            $this->runner->run($test, $operation, $token);
        }
    }

    public function update(string $testId, TestConfig $config, UserContext $context): int
    {
        $existing = $this->testTable->findOneByTenantAndId($context->getTenantId(), (int) $testId);
        if (!$existing instanceof Table\Generated\TestRow) {
            throw new StatusCode\NotFoundException('Could not find test');
        }

        $body = $config->getBody();

        $existing->setUriFragments($config->getUriFragments());
        $existing->setParameters($config->getParameters());
        $existing->setHeaders($config->getHeaders());
        $existing->setBody(isset($body) ? \json_encode($body, JSON_PRETTY_PRINT) : '');
        $this->testTable->update($existing);

        return $existing->getId();
    }
}
