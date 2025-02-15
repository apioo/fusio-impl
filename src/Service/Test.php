<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Service;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use Fusio\Model;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * Test
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Test
{
    public function __construct(
        private Test\Runner $runner,
        private Table\Operation $operationTable,
        private Table\Test $testTable
    ) {
    }

    public function refresh(UserContext $context): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\OperationTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_CATEGORY_ID, $context->getCategoryId());
        $condition->equals(Table\Generated\OperationTable::COLUMN_STATUS, Table\Operation::STATUS_ACTIVE);
        $operations = $this->operationTable->findAll($condition, 0, 1024, Table\Generated\OperationColumn::NAME, OrderBy::ASC);

        $ids = [];
        foreach ($operations as $operation) {
            $existing = $this->testTable->findOneByOperationId($operation->getId());
            if ($existing instanceof Table\Generated\TestRow) {
                $existing->setStatus($existing->getStatus() === Table\Test::STATUS_DISABLED ? Table\Test::STATUS_DISABLED : Table\Test::STATUS_PENDING);
                $this->testTable->update($existing);

                $ids[] = $existing->getId();
            } else {
                $row = new Table\Generated\TestRow();
                $row->setTenantId($context->getTenantId());
                $row->setCategoryId($context->getCategoryId());
                $row->setOperationId($operation->getId());
                $row->setStatus(Table\Test::STATUS_PENDING);
                $this->testTable->create($row);

                $ids[] = $this->testTable->getLastInsertId();
            }
        }

        $condition = Condition::withAnd();
        $condition->notIn(Table\Generated\TestTable::COLUMN_ID, $ids);
        $tests = $this->testTable->findAll($condition, startIndex: 0, count: 1024);
        foreach ($tests as $test) {
            $this->testTable->delete($test);
        }
    }

    public function run(UserContext $context): void
    {
        $token = $this->runner->authenticate($context);

        $condition = Condition::withAnd();
        $condition->notEquals(Table\Test::COLUMN_STATUS, Table\Test::STATUS_DISABLED);
        $condition->notEquals(Table\Test::COLUMN_STATUS, Table\Test::STATUS_SUCCESS);
        $tests = $this->testTable->findAll($condition, 0, 16);
        foreach ($tests as $test) {
            $operation = $this->operationTable->findOneByIdentifier($context->getTenantId(), $context->getCategoryId(), '' . $test->getOperationId());
            if (!$operation instanceof Table\Generated\OperationRow) {
                continue;
            }

            $this->runner->run($test, $operation, $token);
        }
    }

    public function update(string $testId, Model\Backend\Test $test, UserContext $context): int
    {
        $existing = $this->testTable->findOneByTenantAndId($context->getTenantId(), $context->getCategoryId(), (int) $testId);
        if (!$existing instanceof Table\Generated\TestRow) {
            throw new StatusCode\NotFoundException('Could not find test');
        }

        $config = $test->getConfig();
        $body = $config?->getBody();

        $existing->setStatus($test->getStatus() ?? $existing->getStatus());
        $existing->setUriFragments($config?->getUriFragments());
        $existing->setParameters($config?->getParameters());
        $existing->setHeaders($config?->getHeaders());
        $existing->setBody(isset($body) ? Parser::encode($body, JSON_PRETTY_PRINT) : '');
        $this->testTable->update($existing);

        return $existing->getId();
    }
}
