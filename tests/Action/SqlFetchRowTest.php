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

use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Action\SqlFetchRow;
use Fusio\Impl\App;
use Fusio\Impl\Form\Builder;
use Fusio\Impl\Form\Container;
use Fusio\Impl\Tests\ActionTestCaseTrait;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;

/**
 * SqlFetchRowTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlFetchRowTest extends DbTestCase
{
    use ActionTestCaseTrait;

    public function testHandle()
    {
        $action = new SqlFetchRow();
        $action->setConnection(Environment::getService('connection'));
        $action->setConnector(Environment::getService('connector'));
        $action->setTemplateFactory(Environment::getService('template_factory'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'sql'        => 'SELECT * FROM app_news WHERE id = {{ request.uriFragments.get("news_id")|prepare }}',
        ]);

        $response = $action->handle($this->getRequest('GET', ['news_id' => 2]), $parameters, $this->getContext());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals(['id' => 2, 'title' => 'bar', 'content' => 'foo', 'date' => '2015-02-27 19:59:15'], $response->getBody());
    }

    public function testGetForm()
    {
        $action  = new SqlFetchRow();
        $builder = new Builder();
        $factory = Environment::getService('form_element_factory');

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());
    }
}
