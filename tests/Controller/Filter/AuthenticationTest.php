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

namespace Fusio\Impl\Tests\Controller\Filter;

use Fusio\Engine\Model\App;
use Fusio\Engine\Model\User;
use Fusio\Impl\Controller\Filter\Authentication;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Impl\Repository\AppDatabase;
use Fusio\Impl\Service\Security\TokenValidator;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table\Operation;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\UnauthorizedException;
use PSX\Http\Filter\FilterChain;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Sql\TableManagerInterface;
use PSX\Uri\Uri;

/**
 * AuthenticationTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class AuthenticationTest extends DbTestCase
{
    public function testHandle()
    {
        $contextFactory = new ContextFactory();
        $this->newContext($contextFactory->factory());

        $request = new Request(Uri::parse('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0'], 'Authorization' => ['Bearer b41344388feed85bc362e518387fdc8c81b896bfe5e794131e1469770571d873']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->getMock();

        $filterChain->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $tokenValidator = Environment::getService(TokenValidator::class);

        $authentication = new Authentication($tokenValidator, $contextFactory);
        $authentication->handle($request, $response, $filterChain);

        $app = $contextFactory->getActive()->getApp();
        $user = $contextFactory->getActive()->getUser();

        $this->assertInstanceOf(App::class, $app);
        $this->assertEquals('Foo-App', $app->getName());
        $this->assertEquals('5347307d-d801-4075-9aaa-a21a29a448c5', $app->getAppKey());
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Consumer', $user->getName());
    }

    public function testHandleInvalidJWTFormat()
    {
        $this->expectException(\DomainException::class);

        $contextFactory = new ContextFactory();
        $this->newContext($contextFactory->factory());

        $request = new Request(Uri::parse('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0'], 'Authorization' => ['Bearer foo.bar.baz']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->getMock();

        $filterChain->expects($this->never())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $tokenValidator = Environment::getService(TokenValidator::class);

        $authentication = new Authentication($tokenValidator, $contextFactory);
        $authentication->handle($request, $response, $filterChain);
    }

    public function testHandleInvalidToken()
    {
        $this->expectException(UnauthorizedException::class);

        $contextFactory = new ContextFactory();
        $this->newContext($contextFactory->factory());

        $request = new Request(Uri::parse('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0'], 'Authorization' => ['Bearer foobar']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->getMock();

        $filterChain->expects($this->never())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $tokenValidator = Environment::getService(TokenValidator::class);

        $authentication = new Authentication($tokenValidator, $contextFactory);
        $authentication->handle($request, $response, $filterChain);
    }

    public function testHandleInvalidAuthType()
    {
        $this->expectException(UnauthorizedException::class);

        $contextFactory = new ContextFactory();
        $this->newContext($contextFactory->factory());

        $request = new Request(Uri::parse('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0'], 'Authorization' => ['Basic foobar']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->getMock();

        $filterChain->expects($this->never())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $tokenValidator = Environment::getService(TokenValidator::class);

        $authentication = new Authentication($tokenValidator, $contextFactory);
        $authentication->handle($request, $response, $filterChain);
    }

    public function testHandleNoAuthorizationHeader()
    {
        $this->expectException(UnauthorizedException::class);

        $contextFactory = new ContextFactory();
        $this->newContext($contextFactory->factory());

        $request = new Request(Uri::parse('/foo'), 'GET', ['Content-Type' => ['application/json'], 'User-Agent' => ['FooAgent 1.0']]);
        $response = new Response();

        $filterChain = $this->getMockBuilder(FilterChain::class)
            ->getMock();

        $filterChain->expects($this->never())
            ->method('handle')
            ->with($this->equalTo($request), $this->equalTo($response));

        $tokenValidator = Environment::getService(TokenValidator::class);

        $authentication = new Authentication($tokenValidator, $contextFactory);
        $authentication->handle($request, $response, $filterChain);
    }

    private function newContext(Context $context): void
    {
        $id = Fixture::getReference('fusio_operation', 'test.listFoo')->resolve($this->connection);
        $row = Environment::getService(TableManagerInterface::class)->getTable(Operation::class)->find($id);
        $row->setPublic(0);
        $context->setOperation($row);

        $app = (new AppDatabase($this->connection, Environment::getService(FrameworkConfig::class)))->get(1);
        $context->setApp($app);
    }
}
