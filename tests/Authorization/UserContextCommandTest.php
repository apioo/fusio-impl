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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * UserContextCommandTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserContextCommandTest extends DbTestCase
{
    private Command $command;
    private ContextFactory $contextFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->command = new Command();
        $this->contextFactory = Environment::getService(ContextFactory::class);
        $this->contextFactory->addContextOptions($this->command);
    }

    public function testCommandContext()
    {
        $input = new ArrayInput([], $this->command->getDefinition());
        $userContext = $this->contextFactory->newCommandContext($input);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(null, $userContext->getTenantId());
        $this->assertEquals(4, $userContext->getCategoryId());
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(null, $userContext->getAppId());
    }

    public function testCommandContextWithIds()
    {
        $input = new ArrayInput(['--category' => 1, '--user' => 2, '--app' => 2], $this->command->getDefinition());
        $userContext = $this->contextFactory->newCommandContext($input);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(null, $userContext->getTenantId());
        $this->assertEquals(1, $userContext->getCategoryId());
        $this->assertEquals(2, $userContext->getUserId());
        $this->assertEquals(2, $userContext->getAppId());
    }

    public function testCommandContextWithNames()
    {
        $input = new ArrayInput(['--category' => 'default', '--user' => 'Consumer', '--app' => 'Developer'], $this->command->getDefinition());
        $userContext = $this->contextFactory->newCommandContext($input);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(null, $userContext->getTenantId());
        $this->assertEquals(1, $userContext->getCategoryId());
        $this->assertEquals(2, $userContext->getUserId());
        $this->assertEquals(2, $userContext->getAppId());
    }
}
