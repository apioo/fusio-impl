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

use Fusio\Impl\Command\Marketplace\UpgradeCommand;
use Fusio\Impl\Service\System\ContextFactory;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * UpdateCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UpdateCommandTest extends MarketplaceTestCase
{
    public function testCommand()
    {
        if (!is_dir(Environment::getConfig('fusio_apps_dir') . '/fusio')) {
            $this->markTestSkipped('The fusio app is not installed');
        }

        $appsDir = Environment::getConfig('fusio_apps_dir');
        mkdir($appsDir . '/fusio');
        file_put_contents($appsDir . '/fusio/app.yaml', $this->getOldApp());
        file_put_contents($appsDir . '/fusio/index.html', 'old');

        $command = new UpgradeCommand(
            $this->getInstaller(),
            $this->getRemoteRepository(),
            Environment::getService(ContextFactory::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'name' => 'fusio',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Updated app fusio', trim($actual));

        $this->assertDirectoryExists($appsDir . '/fusio');
        $this->assertFileExists($appsDir . '/fusio/app.yaml');
        $this->assertFileExists($appsDir . '/fusio/index.html');
        $this->assertEquals('foobar', file_get_contents($appsDir . '/fusio/index.html'));
    }

    private function getOldApp()
    {
        return <<<YAML
version: '0.6'
description: 'The backend app is the official app to develop, configure and maintain your API.'
screenshot: 'https://raw.githubusercontent.com/apioo/fusio/master/doc/_static/backend.png'
website: 'https://github.com/apioo/fusio-backend'
downloadUrl: 'https://www.fusio-project.org/files/fusio.zip'
sha1Hash: 573cb65ec966ed13f23aaa1888066069c7fdb3ae
YAML;
    }
}
