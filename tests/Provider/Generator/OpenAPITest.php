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

namespace Fusio\Impl\Tests\Provider\Generator;

use Fusio\Engine\Generator\Setup;
use Fusio\Engine\Parameters;
use Fusio\Impl\Provider\Generator\OpenAPI;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Schema\SchemaManager;

/**
 * OpenAPITest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class OpenAPITest extends DbTestCase
{
    public function testSetupPetstore()
    {
        $spec  = file_get_contents(__DIR__ . '/resource/openapi_petstore.json');
        $setup = new Setup();

        (new OpenAPI(new SchemaManager()))->setup($setup, new Parameters(['spec' => $spec]));

        $schemas = $setup->getSchemas();
        $actions = $setup->getActions();
        $operations = $setup->getOperations();

        $this->assertEquals(3, count($schemas));
        $this->assertEquals(3, count($actions));
        $this->assertEquals(3, count($operations));

        $this->assertEquals('Pet', $schemas[0]->getName());
        $this->assertEquals('Pets', $schemas[1]->getName());
        $this->assertEquals('Error', $schemas[2]->getName());

        $this->assertEquals('listPets', $actions[0]->getName());
        $this->assertEquals('http://petstore.swagger.io/v1/pets', $actions[0]->getConfig()['url']);
        $this->assertEquals('createPets', $actions[1]->getName());
        $this->assertEquals('http://petstore.swagger.io/v1/pets', $actions[1]->getConfig()['url']);
        $this->assertEquals('showPetById', $actions[2]->getName());
        $this->assertEquals('http://petstore.swagger.io/v1/pets/:petId', $actions[2]->getConfig()['url']);

        $this->assertEquals('listPets', $operations[0]->getName());
        $this->assertEquals('GET', $operations[0]->getHttpMethod());
        $this->assertEquals('/pets', $operations[0]->getHttpPath());
        $this->assertEquals('createPets', $operations[1]->getName());
        $this->assertEquals('POST', $operations[1]->getHttpMethod());
        $this->assertEquals('/pets', $operations[1]->getHttpPath());
        $this->assertEquals('showPetById', $operations[2]->getName());
        $this->assertEquals('GET', $operations[2]->getHttpMethod());
        $this->assertEquals('/pets/:petId', $operations[2]->getHttpPath());
    }
}
