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

namespace Fusio\Impl\Tests\Provider\Routes;

use Fusio\Engine\Parameters;
use Fusio\Engine\Routes\Setup;
use Fusio\Impl\Provider\Routes\OpenAPI;
use Fusio\Impl\Tests\DbTestCase;

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

        (new OpenAPI())->setup($setup, '/', new Parameters(['spec' => $spec]));

        $schemas = $setup->getSchemas();
        $actions = $setup->getActions();
        $routes = $setup->getRoutes();

        $this->assertEquals(5, count($schemas));
        $this->assertEquals(3, count($actions));
        $this->assertEquals(2, count($routes));

        $this->assertEquals('Pet', $schemas[0]->name);
        $this->assertEquals('Pets', $schemas[1]->name);
        $this->assertEquals('Error', $schemas[2]->name);
        $this->assertEquals('PetsGetQuery', $schemas[3]->name);
        $this->assertEquals('PetsPetIdGetQuery', $schemas[4]->name);

        $this->assertEquals('pets-listPets-GET', $actions[0]->name);
        $this->assertEquals('http://petstore.swagger.io/v1/pets', $actions[0]->config->url);
        $this->assertEquals('pets-createPets-POST', $actions[1]->name);
        $this->assertEquals('http://petstore.swagger.io/v1/pets', $actions[1]->config->url);
        $this->assertEquals('pets-_petId-showPetById-GET', $actions[2]->name);
        $this->assertEquals('http://petstore.swagger.io/v1/pets/:petId', $actions[2]->config->url);

        $this->assertEquals('/pets', $routes[0]->path);
        $this->assertEquals('/pets/:petId', $routes[1]->path);
    }
}
