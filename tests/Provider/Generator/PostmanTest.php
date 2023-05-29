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
use Fusio\Impl\Provider\Generator\Postman;
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
        $operations = $setup->getOperations();

        $this->assertEquals(0, count($schemas));
        $this->assertEquals(132, count($actions));
        $this->assertEquals(132, count($operations));

        $this->assertEquals('Backend_Action_User_Get_get', $actions[0]->getName());
        $this->assertEquals('http://api.fusio.cloud:8080/backend/user/:user_id', $actions[0]->getConfig()['url']);
        $this->assertEquals('Backend_Action_User_Update_update', $actions[1]->getName());
        $this->assertEquals('http://api.fusio.cloud:8080/backend/user/:user_id', $actions[1]->getConfig()['url']);

        $this->assertEquals('Backend_Action_User_Get_get', $operations[0]->getName());
        $this->assertEquals('GET', $operations[0]->getHttpMethod());
        $this->assertEquals('/backend/user/:user_id', $operations[0]->getHttpPath());
        $this->assertEquals('Backend_Action_User_Update_update', $operations[1]->getName());
        $this->assertEquals('PUT', $operations[1]->getHttpMethod());
        $this->assertEquals('/backend/user/:user_id', $operations[1]->getHttpPath());
    }
}
