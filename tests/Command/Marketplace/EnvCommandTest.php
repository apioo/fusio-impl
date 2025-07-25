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

use Fusio\Impl\Command\Marketplace\EnvCommand;
use Fusio\Impl\Service\Marketplace\Factory;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * EnvCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EnvCommandTest extends DbTestCase
{
    public function testCommand()
    {
        if (!is_dir(Environment::getConfig('fusio_apps_dir') . '/fusio')) {
            $this->markTestSkipped('The fusio app is not installed');
        }

        $command = new EnvCommand(
            Environment::getService(Factory::class),
            Environment::getService(ContextFactory::class),
            Environment::getService(FrameworkConfig::class)
        );

        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'name' => 'fusio',
        ]);

        $actual = $commandTester->getDisplay();

        $this->assertEquals('Replaced env fusio/fusio', trim($actual));
    }
}
