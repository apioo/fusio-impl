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

namespace Fusio\Impl\Tests\Service\Generator;

use Fusio\Adapter\Sql\Action\SqlInsert;
use Fusio\Adapter\Sql\Action\SqlSelectAll;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Generator\ProviderInterface;
use Fusio\Engine\Generator\SetupInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaSource;

/**
 * TestProvider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TestProvider implements ProviderInterface
{
    public function getName(): string
    {
        return 'Test-Provider';
    }

    public function setup(SetupInterface $setup, ParametersInterface $configuration): void
    {
        $schema = new SchemaCreate();
        $schema->setName('Schema_Request');
        $schema->setSource(SchemaSource::fromIterable([
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                ],
                'createDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
        ]));
        $setup->addSchema($schema);

        $schema = new SchemaCreate();
        $schema->setName('Schema_Response');
        $schema->setSource(SchemaSource::fromIterable([
            'type' => 'object',
            'properties' => [
                'title' => [
                    'type' => 'string',
                ],
                'createDate' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
        ]));
        $setup->addSchema($schema);

        $action = new ActionCreate();
        $action->setName('Action_Select');
        $action->setClass(SqlSelectAll::class);
        $action->setConfig(ActionConfig::fromIterable([
            'table' => $configuration->get('table'),
        ]));
        $setup->addAction($action);

        $action = new ActionCreate();
        $action->setName('Action_Insert');
        $action->setClass(SqlInsert::class);
        $action->setConfig(ActionConfig::fromIterable([
            'table' => $configuration->get('table'),
        ]));
        $setup->addAction($action);

        $operation = new OperationCreate();
        $operation->setName('getAll');
        $operation->setDescription('Returns all entries on the table');
        $operation->setHttpMethod('GET');
        $operation->setHttpPath('/table');
        $operation->setHttpCode(200);
        $operation->setIncoming('Schema_Request');
        $operation->setOutgoing('Schema_Response');
        $operation->setAction('Action_Select');
        $setup->addOperation($operation);

        $operation = new OperationCreate();
        $operation->setName('create');
        $operation->setDescription('Creates a new entry on the table');
        $operation->setHttpMethod('POST');
        $operation->setHttpPath('/table');
        $operation->setHttpCode(200);
        $operation->setIncoming('Schema_Request');
        $operation->setOutgoing('Schema_Response');
        $operation->setAction('Action_Insert');
        $setup->addOperation($operation);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newInput('table', 'Table'));
    }
}
