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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Engine\Context;
use Fusio\Engine\Model\App;
use Fusio\Engine\Model\User;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\System\ContextFactory;
use PHPUnit\Framework\TestCase;
use PSX\Framework\Test\Environment;

/**
 * UserContextTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserContextTest extends TestCase
{
    public function testAnonymousContext()
    {
        $userContext = Environment::getService(ContextFactory::class)->newAnonymousContext();

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(null, $userContext->getAppId());
    }

    public function testActionContext()
    {
        $app = new App(false, 1, 0, 0 , '', '', '', [], []);
        $user = new User(false, 1, 0, 0, 0, '', '', 0);

        $context = new Context(1, '/', $app, $user);
        $userContext = Environment::getService(ContextFactory::class)->newActionContext($context);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }

    public function testCommandContext()
    {
        $userContext = Environment::getService(ContextFactory::class)->newCommandContext();

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(null, $userContext->getAppId());
    }

    public function testContext()
    {
        $userContext = UserContext::newContext(1, 1, 1);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getCategoryId());
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }
}
