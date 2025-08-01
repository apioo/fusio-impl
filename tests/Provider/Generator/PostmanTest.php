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

namespace Fusio\Impl\Tests\Provider\Generator;

use Fusio\Engine\Generator\Setup;
use Fusio\Engine\Parameters;
use Fusio\Impl\Provider\Generator\Postman;
use Fusio\Impl\Tests\DbTestCase;

/**
 * PostmanTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class PostmanTest extends DbTestCase
{
    public function testSetup()
    {
        $import = file_get_contents(__DIR__ . '/resource/postman.json');
        $setup = new Setup();

        (new Postman())->setup($setup, new Parameters(['import' => $import]));

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
