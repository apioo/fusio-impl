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

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\ResponseInterface;
use Fusio\Impl\Action\MqAmqp;
use Fusio\Impl\App;
use Fusio\Impl\Form\Builder;
use Fusio\Impl\Form\Container;
use Fusio\Impl\Tests\ActionTestCaseTrait;
use Fusio\Impl\Tests\DbTestCase;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PSX\Framework\Test\Environment;
use PSX\Record\Record;

/**
 * MqAmqpTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MqAmqpTest extends DbTestCase
{
    use ActionTestCaseTrait;

    public function testHandle()
    {
        // channel
        $channel = $this->getMock(AMQPChannel::class, ['basic_publish'], [], '', false);

        $channel->expects($this->once())
            ->method('basic_publish')
            ->with($this->callback(function ($message) {
                /** @var \PhpAmqpLib\Message\AMQPMessage $message */
                $this->assertInstanceOf(AMQPMessage::class, $message);
                $this->assertEquals(['content_type' => 'application/json', 'delivery_mode' => 2], $message->get_properties());
                $this->assertJsonStringEqualsJsonString('{"foo": "bar"}', $message->body);

                return true;
            }), $this->equalTo(''), $this->equalTo('foo'));

        // connection
        $connection = $this->getMock(AMQPStreamConnection::class, ['channel'], [], '', false);

        $connection->expects($this->once())
            ->method('channel')
            ->will($this->returnValue($channel));

        // connector
        $connector = $this->getMock(ConnectorInterface::class, ['getConnection'], [], '', false);

        $connector->expects($this->once())
            ->method('getConnection')
            ->with($this->equalTo(1))
            ->will($this->returnValue($connection));

        $action = new MqAmqp();
        $action->setConnector($connector);
        $action->setResponse(Environment::getService('response'));

        $parameters = $this->getParameters([
            'connection' => 1,
            'queue'      => 'foo',
        ]);

        $body = Record::fromArray([
            'foo' => 'bar'
        ]);

        $response = $action->handle($this->getRequest('POST', [], [], [], $body), $parameters, $this->getContext());

        $body = [
            'success' => true,
            'message' => 'Push was successful'
        ];

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals([], $response->getHeaders());
        $this->assertEquals($body, $response->getBody());
    }

    public function testGetForm()
    {
        $action  = new MqAmqp();
        $builder = new Builder();
        $factory = Environment::getService('form_element_factory');

        $action->configure($builder, $factory);

        $this->assertInstanceOf(Container::class, $builder->getForm());
    }
}
