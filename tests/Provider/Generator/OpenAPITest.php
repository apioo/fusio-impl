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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
