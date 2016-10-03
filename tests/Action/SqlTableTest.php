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
use Fusio\Impl\Action\SqlTable;
use Fusio\Impl\App;
use Fusio\Engine\Form\Builder;
use Fusio\Engine\Form\Container;
use Fusio\Engine\Test\EngineTestCaseTrait;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Record\Record;

/**
 * SqlTableTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlTableTest extends DbTestCase
{
    use EngineTestCaseTrait;

    public function testHandleGetCollection()
    {
        $action = new SqlTable();
        $action->setConnector(Environment::getService('connector'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'table'      => 'app_news',
            'columns'    => 'id,title,content,date',
            'primaryKey' => 'id',
        ]);

        $response = $action->handle($this->getRequest(), $parameters, $this->getContext());

        $result = [
            'totalResults' => 2,
            'itemsPerPage' => 16,
            'startIndex' => 0,
            'entry' => [[
                'id' => 2,
                'title' => 'bar',
                'content' => 'foo',
                'date' => '2015-02-27 19:59:15',
            ],[
                'id' => 1,
                'title' => 'foo',
                'content' => 'bar',
                'date' => '2015-02-27 19:59:15',
            ]]
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($result, $response->getBody());
    }

    public function testHandleGetEntity()
    {
        $action = new SqlTable();
        $action->setConnector(Environment::getService('connector'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'table'      => 'app_news',
            'columns'    => 'id,title,content,date',
            'primaryKey' => 'id',
        ]);

        $response = $action->handle($this->getRequest('GET', ['id' => 1]), $parameters, $this->getContext());

        $result = [
            'id' => '1',
            'title' => 'foo',
            'content' => 'bar',
            'date' => '2015-02-27 19:59:15',
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($result, $response->getBody());
    }

    public function testHandlePost()
    {
        $action = new SqlTable();
        $action->setConnector(Environment::getService('connector'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'table'      => 'app_news',
            'columns'    => 'id,title,content,date',
            'primaryKey' => 'id',
        ]);

        $body = new Record();
        $body['title'] = 'lorem';
        $body['content'] = 'ipsum';
        $body['date'] = '2015-02-27 19:59:15';

        $response = $action->handle($this->getRequest('POST', [], [], [], $body), $parameters, $this->getContext());

        $result = [
            'success' => true,
            'message' => 'Entry successful created',
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($result, $response->getBody());
        
        // check whether the entry was inserted
        $row    = Environment::getService('connection')->fetchAssoc('SELECT * FROM app_news ORDER BY id DESC');
        $expect = [
            'id'      => 3,
            'title'   => 'lorem',
            'content' => 'ipsum',
            'date'    => '2015-02-27 19:59:15',
        ];

        $this->assertEquals($expect, $row);
    }

    public function testHandlePut()
    {
        $action = new SqlTable();
        $action->setConnector(Environment::getService('connector'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'table'      => 'app_news',
            'columns'    => 'id,title,content,date',
            'primaryKey' => 'id',
        ]);

        $body = new Record();
        $body['title'] = 'lorem';
        $body['content'] = 'ipsum';
        $body['date'] = '2015-02-27 19:59:15';

        $response = $action->handle($this->getRequest('PUT', ['id' => 1], [], [], $body), $parameters, $this->getContext());

        $result = [
            'success' => true,
            'message' => 'Entry successful updated',
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($result, $response->getBody());

        // check whether the entry was inserted
        $row    = Environment::getService('connection')->fetchAssoc('SELECT * FROM app_news WHERE id = 1');
        $expect = [
            'id'      => 1,
            'title'   => 'lorem',
            'content' => 'ipsum',
            'date'    => '2015-02-27 19:59:15',
        ];

        $this->assertEquals($expect, $row);
    }

    public function testHandleDelete()
    {
        $action = new SqlTable();
        $action->setConnector(Environment::getService('connector'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'table'      => 'app_news',
            'columns'    => 'id,title,content,date',
            'primaryKey' => 'id',
        ]);

        $response = $action->handle($this->getRequest('DELETE', ['id' => 1]), $parameters, $this->getContext());

        $result = [
            'success' => true,
            'message' => 'Entry successful deleted',
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($result, $response->getBody());

        $row = Environment::getService('connection')->fetchAssoc('SELECT * FROM app_news WHERE id = 1');

        $this->assertEmpty($row);
    }

    public function testGetForm()
    {
        $action  = new SqlTable();
        $builder = new Builder();
        $factory = Environment::getService('form_element_factory');

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());
    }
}
