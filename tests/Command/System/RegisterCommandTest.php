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
 * @license http://www.gnu.org/licenses/agpl-3.0
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
            \Fusio\Impl\Tests\Adapter\TestAdapter::class,
        ];

        $this->assertEquals($expect, $actual);
    }
}
