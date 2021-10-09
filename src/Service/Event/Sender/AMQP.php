<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Event\Sender;

use Fusio\Impl\Service\Event\Message;
use Fusio\Impl\Service\Event\SenderInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * AMQP
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class AMQP implements SenderInterface
{
    /**
     * @var string
     */
    protected $queue;

    /**
     * @var string
     */
    protected $exchange;

    /**
     * @param string $queue
     * @param string $exchange
     */
    public function __construct(string $queue, string $exchange)
    {
        $this->queue    = $queue;
        $this->exchange = $exchange;
    }

    /**
     * @inheritdoc
     */
    public function accept($dispatcher)
    {
        return $dispatcher instanceof AMQPStreamConnection;
    }

    /**
     * @param \PhpAmqpLib\Connection\AMQPStreamConnection $dispatcher
     * @param \Fusio\Impl\Service\Event\Message $message
     * @return integer
     */
    public function send($dispatcher, Message $message)
    {
        $channel = $dispatcher->channel();
        $channel->queue_declare($this->queue, false, true, false, false);
        $channel->exchange_declare($this->exchange, 'direct', false, true, false);
        $channel->queue_bind($this->queue, $this->exchange);

        $body = \json_encode([
            'endpoint' => $message->getEndpoint(),
            'payload'  => \json_decode($message->getPayload()),
        ]);

        $properties = [
            'content_type'  => 'application/json',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ];

        $channel->basic_publish(new AMQPMessage($body, $properties), $this->exchange);
        $channel->close();

        return 200;
    }
}
