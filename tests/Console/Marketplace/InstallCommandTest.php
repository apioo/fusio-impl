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

namespace Fusio\Impl\Tests\Console\Marketplace;

use Fusio\Impl\Console\Marketplace\InstallCommand;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * InstallCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class InstallCommandTest extends MarketplaceTestCase
{
    public function testCommand()
    {
        if (is_dir(Environment::getConfig()->get('fusio_apps_dir') . '/fusio')) {
            $this->markTestSkipped('The fusio app is already installed');
        }

        $command = new InstallCommand(
            $this->getInstaller(),
            $this->getRemoteRepository()
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'name' => 'fusio',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Installed app fusio', trim($actual));

        $appsDir = Environment::getConfig()->get('fusio_apps_dir');
        $this->assertDirectoryExists($appsDir . '/fusio');
        $this->assertFileExists($appsDir . '/fusio/app.yaml');
        $this->assertFileExists($appsDir . '/fusio/index.html');
        $this->assertEquals('foobar', file_get_contents($appsDir . '/fusio/index.html'));
    }
}
