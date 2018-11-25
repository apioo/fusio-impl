<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Base;
use Fusio\Impl\Service\Event\Message;
use Fusio\Impl\Service\Event\SenderInterface;
use GuzzleHttp\Client;

/**
 * Guzzle
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Guzzle implements SenderInterface
{
    /**
     * @inheritdoc
     */
    public function accept($dispatcher)
    {
        return $dispatcher instanceof Client;
    }

    /**
     * @param \GuzzleHttp\Client $dispatcher
     * @param \Fusio\Impl\Service\Event\Message $message
     * @return integer
     */
    public function send($dispatcher, Message $message)
    {
        $response = $dispatcher->post($message->getEndpoint(), [
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent'   => Base::getUserAgent(),
            ],
            'body' => $message->getPayload(),
        ]);

        return $response->getStatusCode();
    }
}
