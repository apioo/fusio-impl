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

namespace Fusio\Impl\Service\GraphQL;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Table;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use PSX\Sql\Condition;

/**
 * TypeConfigDecorator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class TypeConfigDecorator
{
    public function __construct(
        private Table\Operation $operationTable,
        private ContextFactory $contextFactory,
    ) {
    }

    public function __invoke(array $typeConfig): array
    {
        switch ($typeConfig['name']) {
            case 'Query':
                $typeConfig['fields'] = function () use ($typeConfig): array {
                    $fields = $typeConfig['fields']();

                    $context = $this->contextFactory->getActive();

                    $condition = Condition::withAnd();
                    $condition->equals(Table\Generated\OperationColumn::TENANT_ID, $context->getTenantId());
                    $condition->equals(Table\Generated\OperationColumn::CATEGORY_ID, 1);
                    $condition->equals(Table\Generated\OperationColumn::HTTP_METHOD, 'GET');
                    $operations = $this->operationTable->findBy($condition);
                    foreach ($operations as $operation) {
                        $fields[$operation->getName()]['resolve'] = function () {
                            // @TODO resolve arguments
                        };
                    }

                    return $fields;
                };

                return $typeConfig;

            case 'Track':
                $typeConfig['fields'] = function () use ($typeConfig): array {
                    $fields = $typeConfig['fields']();
                    // @TODO resolve model fields
                    // $fields['author']['resolve'] = fn (array $track): array => Author::find($track['authorId']);

                    return $fields;
                };

                return $typeConfig;
        }

        return [];
    }
}
