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

namespace Fusio\Impl\Tests\Command\Marketplace;

use Fusio\Impl\Command\Marketplace\InstallCommand;
use Fusio\Impl\Service\Marketplace\Factory;
use Fusio\Impl\Service\Marketplace\Installer;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * InstallCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InstallCommandTest extends DbTestCase
{
    public function testCommandApp()
    {
        if (is_dir(Environment::getConfig('fusio_apps_dir') . '/fusio')) {
            $this->markTestSkipped('The fusio app is already installed');
        }

        $command = new InstallCommand(
            Environment::getService(Installer::class),
            Environment::getService(Factory::class),
            Environment::getService(ContextFactory::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'type' => 'fusio',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Installed app fusio/fusio', trim($actual));

        $appsDir = Environment::getConfig('fusio_apps_dir');
        $this->assertDirectoryExists($appsDir . '/fusio');
        $this->assertFileExists($appsDir . '/fusio/app.yaml');
        $this->assertFileExists($appsDir . '/fusio/index.html');

        $content = file_get_contents($appsDir . '/fusio/index.html');
        $this->assertStringContainsString('FUSIO_URL', $content);
        $this->assertStringContainsString('FUSIO_APP_KEY', $content);
    }

    public function testCommandAction()
    {
        $command = new InstallCommand(
            Environment::getService(Installer::class),
            Environment::getService(Factory::class),
            Environment::getService(ContextFactory::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'type' => 'action',
            'name' => 'fusio/BulkInsert',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Installed action fusio/BulkInsert', trim($actual));

        $row = $this->connection->fetchAssociative('SELECT id, class, config FROM fusio_action WHERE name = :name', [
            'name' => 'fusio-BulkInsert',
        ]);

        $this->assertNotEmpty($row);
        $this->assertEquals('Fusio.Adapter.Worker.Action.WorkerPHPLocal', $row['class']);
    }
}
