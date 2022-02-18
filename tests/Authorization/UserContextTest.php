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

namespace Fusio\Impl\Tests\Authorization;

use Fusio\Engine\Context;
use Fusio\Engine\Model\App;
use Fusio\Engine\Model\User;
use Fusio\Impl\Authorization\UserContext;
use PHPUnit\Framework\TestCase;

/**
 * UserContextTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class UserContextTest extends TestCase
{
    public function testAnonymousContext()
    {
        $userContext = UserContext::newAnonymousContext();

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }

    public function testActionContext()
    {
        $app = new App(false, 1, 0, 0 , '', '', '', [], []);
        $user = new User(false, 1, 0, 0, 0, '', '', 0);

        $context = new Context(1, '/', $app, $user);
        $userContext = UserContext::newActionContext($context);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }

    public function testCommandContext()
    {
        $userContext = UserContext::newCommandContext();

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }

    public function testContext()
    {
        $userContext = UserContext::newContext(1, 1);

        $this->assertInstanceOf(UserContext::class, $userContext);
        $this->assertEquals(1, $userContext->getUserId());
        $this->assertEquals(1, $userContext->getAppId());
    }
}
