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
use Fusio\Impl\Provider\Generator\Insomnia;
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
