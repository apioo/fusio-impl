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
use Fusio\Impl\Action\SqlBuilder;
use Fusio\Impl\Action\SqlFetchAll;
use Fusio\Impl\App;
use Fusio\Impl\Form\Builder;
use Fusio\Impl\Form\Container;
use Fusio\Impl\Tests\ActionTestCaseTrait;
use Fusio\Impl\Tests\DbTestCase;
use PSX\Data\Record\Transformer;
use PSX\Framework\Test\Environment;

/**
 * SqlBuilderTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SqlBuilderTest extends DbTestCase
{
    use ActionTestCaseTrait;

    public function testHandle()
    {
        $action = new SqlBuilder();
        $action->setConnection(Environment::getService('connection'));
        $action->setConnector(Environment::getService('connector'));
        $action->setTemplateFactory(Environment::getService('template_factory'));
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'definition' => json_encode([
                'totalCount' => [
                    '!value' => 'SELECT COUNT(*) AS cnt FROM app_news'
                ],
                'entry' => [
                    '!collection' => 'SELECT id, title, content, date FROM app_news ORDER BY id DESC',
                    'definition' => [
                        'id' => 'id|integer',
                        'title' => 'title',
                        'content' => 'content',
                        'date' => 'date|datetime',
                    ]
                ]
            ]),
        ]);

        $response = $action->handle($this->getRequest(), $parameters, $this->getContext());

        $body   = json_encode(Transformer::toArray($response->getBody()), JSON_PRETTY_PRINT);
        $expect = <<<JSON
{
    "totalCount": 2,
    "entry": [
        {
            "id": 2,
            "title": "bar",
            "content": "foo",
            "date": "2015-02-27T19:59:15+00:00"
        },
        {
            "id": 1,
            "title": "foo",
            "content": "bar",
            "date": "2015-02-27T19:59:15+00:00"
        }
    ]
}
JSON;

        
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertJsonStringEqualsJsonString($expect, $body, $body);
    }

    public function testGetForm()
    {
        $action  = new SqlFetchAll();
        $builder = new Builder();
        $factory = Environment::getService('form_element_factory');

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());
    }
}
