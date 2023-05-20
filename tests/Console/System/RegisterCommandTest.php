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

namespace Fusio\Impl\Tests\Console\System;

use Fusio\Adapter;
use Fusio\Impl\Provider\ProviderConfig;
use Fusio\Impl\Tests\Adapter\Test\VoidAction;
use Fusio\Impl\Tests\Adapter\Test\VoidConnection;
use Fusio\Impl\Tests\Adapter\TestAdapter;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
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
        $command = Environment::getService('console')->find('system:register');
        $answers = ['y', '1'];

        $commandTester = new CommandTester($command);
        $commandTester->setInputs($answers);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $file   = Environment::getService('config')->get('fusio_provider');
        $config = ProviderConfig::fromFile($file);

        $actual = array_values($config->get(ProviderConfig::TYPE_ACTION));
        $expect = [
            Adapter\Cli\Action\CliProcessor::class,
            Adapter\Fcgi\Action\FcgiProcessor::class,
            Adapter\File\Action\FileProcessor::class,
            Adapter\GraphQL\Action\GraphQLProcessor::class,
            Adapter\Http\Action\HttpProcessor::class,
            Adapter\Php\Action\PhpProcessor::class,
            Adapter\Php\Action\PhpSandbox::class,
            Adapter\Smtp\Action\SmtpSend::class,
            Adapter\Sql\Action\SqlSelectAll::class,
            Adapter\Sql\Action\SqlSelectRow::class,
            Adapter\Sql\Action\SqlInsert::class,
            Adapter\Sql\Action\SqlUpdate::class,
            Adapter\Sql\Action\SqlDelete::class,
            Adapter\Sql\Action\Query\SqlQueryAll::class,
            Adapter\Sql\Action\Query\SqlQueryRow::class,
            Adapter\Util\Action\UtilStaticResponse::class,
            VoidAction::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection class
        $actual = array_values($config->get(ProviderConfig::TYPE_CONNECTION));
        $expect = [
            Adapter\GraphQL\Connection\GraphQL::class,
            Adapter\Http\Connection\Http::class,
            Adapter\Smtp\Connection\Smtp::class,
            Adapter\Soap\Connection\Soap::class,
            Adapter\Sql\Connection\Sql::class,
            Adapter\Sql\Connection\SqlAdvanced::class,
            VoidConnection::class,
        ];

        $this->assertEquals($expect, $actual);
    }

    public function testCommandAutoConfirm()
    {
        $command = Environment::getService('console')->find('system:register');

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            'class'   => TestAdapter::class,
            '--yes'   => true,
        ]);

        $display = $commandTester->getDisplay();

        $this->assertRegExp('/Registration successful/', $display, $display);

        // check action class
        $file   = Environment::getService('config')->get('fusio_provider');
        $config = ProviderConfig::fromFile($file);

        $actual = array_values($config->get(ProviderConfig::TYPE_ACTION));
        $expect = [
            Adapter\Cli\Action\CliProcessor::class,
            Adapter\Fcgi\Action\FcgiProcessor::class,
            Adapter\File\Action\FileProcessor::class,
            Adapter\GraphQL\Action\GraphQLProcessor::class,
            Adapter\Http\Action\HttpProcessor::class,
            Adapter\Php\Action\PhpProcessor::class,
            Adapter\Php\Action\PhpSandbox::class,
            Adapter\Smtp\Action\SmtpSend::class,
            Adapter\Sql\Action\SqlSelectAll::class,
            Adapter\Sql\Action\SqlSelectRow::class,
            Adapter\Sql\Action\SqlInsert::class,
            Adapter\Sql\Action\SqlUpdate::class,
            Adapter\Sql\Action\SqlDelete::class,
            Adapter\Sql\Action\Query\SqlQueryAll::class,
            Adapter\Sql\Action\Query\SqlQueryRow::class,
            Adapter\Util\Action\UtilStaticResponse::class,
            VoidAction::class,
        ];

        $this->assertEquals($expect, $actual);

        // check connection class
        $actual = array_values($config->get(ProviderConfig::TYPE_CONNECTION));
        $expect = [
            Adapter\GraphQL\Connection\GraphQL::class,
            Adapter\Http\Connection\Http::class,
            Adapter\Smtp\Connection\Smtp::class,
            Adapter\Soap\Connection\Soap::class,
            Adapter\Sql\Connection\Sql::class,
            Adapter\Sql\Connection\SqlAdvanced::class,
            VoidConnection::class,
        ];

        $this->assertEquals($expect, $actual);
    }
}
