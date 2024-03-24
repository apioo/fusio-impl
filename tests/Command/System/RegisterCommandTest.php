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

namespace Fusio\Impl\Tests\Command\System;

use Fusio\Adapter;
use Fusio\Impl\Tests\Adapter\TestAdapter;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * RegisterCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RegisterCommandTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testCommand()
    {
        $command = Environment::getService(Application::class)->find('system:register');
        $answers = ['y', '1'];

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($answers);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertMatchesRegularExpression('/Registration successful/', $display, $display);

        // check action class
        $file = Environment::getService(ConfigInterface::class)->get('fusio_provider');

        $actual = include $file;
        $expect = [
            \Fusio\Adapter\Cli\Adapter::class,
            \Fusio\Adapter\Fcgi\Adapter::class,
            \Fusio\Adapter\File\Adapter::class,
            \Fusio\Adapter\GraphQL\Adapter::class,
            \Fusio\Adapter\Http\Adapter::class,
            \Fusio\Adapter\Php\Adapter::class,
            \Fusio\Adapter\Smtp\Adapter::class,
            \Fusio\Adapter\Soap\Adapter::class,
            \Fusio\Adapter\Sql\Adapter::class,
            \Fusio\Adapter\Util\Adapter::class,
            \Fusio\Adapter\Worker\Adapter::class,
            \Fusio\Impl\Tests\Adapter\TestAdapter::class,
        ];

        $this->assertEquals($expect, $actual);
    }

    public function testCommandAutoConfirm()
    {
        $command = Environment::getService(Application::class)->find('system:register');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
            '--yes'   => true,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertMatchesRegularExpression('/Registration successful/', $display, $display);

        // check action class
        $file = Environment::getService(ConfigInterface::class)->get('fusio_provider');

        $actual = include $file;
        $expect = [
            \Fusio\Adapter\Cli\Adapter::class,
            \Fusio\Adapter\Fcgi\Adapter::class,
            \Fusio\Adapter\File\Adapter::class,
            \Fusio\Adapter\GraphQL\Adapter::class,
            \Fusio\Adapter\Http\Adapter::class,
            \Fusio\Adapter\Php\Adapter::class,
            \Fusio\Adapter\Smtp\Adapter::class,
            \Fusio\Adapter\Soap\Adapter::class,
            \Fusio\Adapter\Sql\Adapter::class,
            \Fusio\Adapter\Util\Adapter::class,
            \Fusio\Adapter\Worker\Adapter::class,
            \Fusio\Impl\Tests\Adapter\TestAdapter::class,
        ];

        $this->assertEquals($expect, $actual);
    }
}
