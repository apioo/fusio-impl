<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Command\Marketplace;

use Fusio\Impl\Command\Marketplace\ListCommand;
use Fusio\Impl\Service\Marketplace\Factory;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * ListCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ListCommandTest extends DbTestCase
{
    public function testCommandApp()
    {
        $command = new ListCommand(
            Environment::getService(Factory::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $actual = $commandTester->getDisplay();

        $apps = ['fusio'];
        foreach ($apps as $appName) {
            $this->assertTrue(strpos($actual, $appName) !== false, $actual);
        }
    }

    public function testCommandAction()
    {
        $command = new ListCommand(
            Environment::getService(Factory::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute(['type' => 'action', 'query' => 'BulkInsert']);

        $actual = $commandTester->getDisplay();

        $actions = ['BulkInsert'];
        foreach ($actions as $actionName) {
            $this->assertTrue(strpos($actual, $actionName) !== false, $actual);
        }
    }
}
