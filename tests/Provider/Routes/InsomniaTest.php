<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Provider\Routes\Insomnia;
use Fusio\Impl\Provider\Routes\OpenAPI;
use Fusio\Impl\Provider\Routes\Postman;
use Fusio\Impl\Tests\DbTestCase;

/**
 * InsomniaTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InsomniaTest extends DbTestCase
{
    public function testSetup()
    {
        $import = file_get_contents(__DIR__ . '/resource/insomnia.json');
        $setup = new Setup();

        (new Insomnia())->setup($setup, '/', new Parameters(['import' => $import]));

        $schemas = $setup->getSchemas();
        $actions = $setup->getActions();
        $routes = $setup->getRoutes();

        $this->assertEquals(0, count($schemas));
        $this->assertEquals(21, count($actions));
        $this->assertEquals(9, count($routes));

        $this->assertEquals('GET-_', $actions[0]->name);
        $this->assertEquals('https://3ca114.fusio.cloud/', $actions[0]->config->url);
        $this->assertEquals('GET-_contract', $actions[1]->name);
        $this->assertEquals('https://3ca114.fusio.cloud/contract', $actions[1]->config->url);
        $this->assertEquals('POST-_contract', $actions[2]->name);
        $this->assertEquals('https://3ca114.fusio.cloud/contract', $actions[2]->config->url);
        $this->assertEquals('GET-_contract__contract_', $actions[3]->name);
        $this->assertEquals('https://3ca114.fusio.cloud/contract/:contract', $actions[3]->config->url);

        $this->assertEquals('/', $routes[0]->path);
        $this->assertEquals('/contract', $routes[1]->path);
        $this->assertEquals('/contract/:contract', $routes[2]->path);
        $this->assertEquals('/customer', $routes[3]->path);
    }
}
