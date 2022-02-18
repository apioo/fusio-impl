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
use Fusio\Impl\Provider\Routes\Postman;
use Fusio\Impl\Tests\DbTestCase;

/**
 * PostmanTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PostmanTest extends DbTestCase
{
    public function testSetup()
    {
        $import = file_get_contents(__DIR__ . '/resource/postman.json');
        $setup = new Setup();

        (new Postman())->setup($setup, '/', new Parameters(['import' => $import]));

        $schemas = $setup->getSchemas();
        $actions = $setup->getActions();
        $routes = $setup->getRoutes();

        $this->assertEquals(19, count($schemas));
        $this->assertEquals(132, count($actions));
        $this->assertEquals(73, count($routes));

        $this->assertEquals('Backend_Action_User_Get_GET', $actions[0]->name);
        $this->assertEquals('http://api.fusio.cloud:8080/backend/user/:user_id', $actions[0]->config->url);
        $this->assertEquals('Backend_Action_User_Update_PUT', $actions[1]->name);
        $this->assertEquals('http://api.fusio.cloud:8080/backend/user/:user_id', $actions[1]->config->url);

        $this->assertEquals('/backend/user/:user_id', $routes[0]->path);
        $this->assertEquals('/backend/user', $routes[1]->path);
    }
}
