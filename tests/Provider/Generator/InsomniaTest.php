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
use Fusio\Impl\Provider\Generator\Insomnia;
use Fusio\Impl\Tests\DbTestCase;

/**
 * InsomniaTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InsomniaTest extends DbTestCase
{
    public function testSetup()
    {
        $import = file_get_contents(__DIR__ . '/resource/insomnia.json');
        $setup = new Setup();

        (new Insomnia())->setup($setup, new Parameters(['import' => $import]));

        $schemas = $setup->getSchemas();
        $actions = $setup->getActions();
        $operations = $setup->getOperations();

        $this->assertEquals(0, count($schemas));
        $this->assertEquals(21, count($actions));
        $this->assertEquals(21, count($operations));

        $this->assertEquals('get', $actions[0]->getName());
        $this->assertEquals('https://3ca114.fusio.cloud/', $actions[0]->getConfig()['url']);
        $this->assertEquals('contract.get', $actions[1]->getName());
        $this->assertEquals('https://3ca114.fusio.cloud/contract', $actions[1]->getConfig()['url']);
        $this->assertEquals('contract.create', $actions[2]->getName());
        $this->assertEquals('https://3ca114.fusio.cloud/contract', $actions[2]->getConfig()['url']);
        $this->assertEquals('contract._contract_.get', $actions[3]->getName());
        $this->assertEquals('https://3ca114.fusio.cloud/contract/:contract', $actions[3]->getConfig()['url']);

        $this->assertEquals('get', $operations[0]->getName());
        $this->assertEquals('GET', $operations[0]->getHttpMethod());
        $this->assertEquals('/', $operations[0]->getHttpPath());
        $this->assertEquals('contract.get', $operations[1]->getName());
        $this->assertEquals('GET', $operations[1]->getHttpMethod());
        $this->assertEquals('/contract', $operations[1]->getHttpPath());
        $this->assertEquals('contract.create', $operations[2]->getName());
        $this->assertEquals('POST', $operations[2]->getHttpMethod());
        $this->assertEquals('/contract', $operations[2]->getHttpPath());
        $this->assertEquals('contract._contract_.get', $operations[3]->getName());
        $this->assertEquals('GET', $operations[3]->getHttpMethod());
        $this->assertEquals('/contract/:contract', $operations[3]->getHttpPath());
    }
}
