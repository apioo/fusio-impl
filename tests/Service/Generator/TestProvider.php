<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
use Fusio\Model\Backend\Action;
use Fusio\Model\Backend\ActionConfig;
use Fusio\Model\Backend\ActionCreate;
use Fusio\Model\Backend\Operation;
use Fusio\Model\Backend\OperationCreate;
use Fusio\Model\Backend\Schema;
use Fusio\Model\Backend\SchemaCreate;
use Fusio\Model\Backend\SchemaSource;

/**
 * TestProvider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class TestProvider implements ProviderInterface
{
    public function getName(): string
    {
        return 'Test-Provider';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration): void
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
        $action->setEngine(PhpClass::class);
        $action->setConfig(ActionConfig::fromIterable([
            'table' => $configuration->get('table'),
        ]));
        $setup->addAction($action);

        $action = new ActionCreate();
        $action->setName('Action_Insert');
        $action->setClass(SqlInsert::class);
        $action->setEngine(PhpClass::class);
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
