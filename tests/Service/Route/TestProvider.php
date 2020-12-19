<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Service\Route;

use Fusio\Adapter\Sql\Action\SqlInsert;
use Fusio\Adapter\Sql\Action\SqlSelectAll;
use Fusio\Engine\Factory\Resolver\PhpClass;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Routes\ProviderInterface;
use Fusio\Engine\Routes\SetupInterface;
use Fusio\Impl\Controller\SchemaApiController;

/**
 * TestProvider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class TestProvider implements ProviderInterface
{
    public function getName()
    {
        return 'Test-Provider';
    }

    public function setup(SetupInterface $setup, string $basePath, ParametersInterface $configuration)
    {
        $schemaRequest = $setup->addSchema('Provider_Schema_Request', [
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
        ]);

        $schemaResponse = $setup->addSchema('Provider_Schema_Response', [
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
        ]);

        $selectAction = $setup->addAction('Provider_Action_Select', SqlSelectAll::class, PhpClass::class, [
            'table' => $configuration->get('table'),
        ]);

        $insertAction = $setup->addAction('Provider_Action_Insert', SqlInsert::class, PhpClass::class, [
            'table' => $configuration->get('table'),
        ]);

        $setup->addRoute(1, '/table', SchemaApiController::class, ['foo', 'bar'], [
            [
                'version' => 1,
                'status' => 4,
                'methods' => [
                    'GET' => [
                        'active' => true,
                        'public' => true,
                        'description' => 'Returns all entries on the table',
                        'request' => $schemaRequest,
                        'responses' => [
                            200 => $schemaResponse,
                        ],
                        'action' => $selectAction,
                    ],
                    'POST' => [
                        'active' => true,
                        'public' => false,
                        'description' => 'Creates a new entry on the table',
                        'request' => $schemaRequest,
                        'responses' => [
                            200 => $schemaResponse,
                        ],
                        'action' => $insertAction,
                    ]
                ],
            ]
        ]);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory)
    {
        $builder->add($elementFactory->newInput('table', 'Table'));
    }
}
