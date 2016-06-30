<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Tests\Action;

use Fusio\Impl\Action\Condition;
use Fusio\Impl\Tests\ActionTestCaseTrait;
use Fusio\Impl\App;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Impl\Form\Builder;
use PSX\Record\Record;
use PSX\Framework\Test\Environment;

/**
 * ConditionTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ConditionTest extends DbTestCase
{
    use ActionTestCaseTrait;

    public function testHandle()
    {
        $action = new Condition();
        $action->setConnection(Environment::getService('connection'));
        $action->setProcessor(Environment::getService('processor'));
        $action->setCache(Environment::getService('cache'));

        $expression = 'rateLimit.getRequestsPerMonth() == 2 && ';
        $expression.= 'rateLimit.getRequestsPerDay() == 2 && ';
        $expression.= 'rateLimit.getRequestsOfRoutePerMonth() == 0 && ';
        $expression.= 'rateLimit.getRequestsOfRoutePerDay() == 0 && ';
        $expression.= 'app.getName() == "Foo-App" && ';
        $expression.= 'app.getParameter("foo") == "bar" && ';
        $expression.= 'uriFragments.get("news_id") == 1 && ';
        $expression.= 'parameters.get("count") == 4 && ';
        $expression.= 'body.get("foo") == "bar" ';

        $parameters = $this->getParameters([
            'condition' => $expression,
            'true'      => 3,
            'false'     => 0,
        ]);

        $body = Record::fromArray([
            'foo' => 'bar'
        ]);

        $response = $action->handle($this->getRequest('POST', ['news_id' => 1], ['count' => 4], [], $body), $parameters, $this->getContext());

        $this->assertInstanceOf('Fusio\Engine\ResponseInterface', $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals(['id' => 1, 'title' => 'foo', 'content' => 'bar', 'date' => '2015-02-27 19:59:15'], $response->getBody());
    }

    public function testGetForm()
    {
        $action  = new Condition();
        $builder = new Builder();
        $factory = Environment::getService('form_element_factory');

        $action->configure($builder, $factory);

        $this->assertInstanceOf('Fusio\Impl\Form\Container', $builder->getForm());
    }
}
