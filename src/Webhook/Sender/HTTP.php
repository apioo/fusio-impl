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

namespace Fusio\Impl\Webhook\Sender;

use Fusio\Impl\Base;
use Fusio\Impl\Webhook\Message;
use Fusio\Impl\Webhook\SenderInterface;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Request;
use PSX\Uri\Url;

/**
 * HTTP
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class HTTP implements SenderInterface
{
    public function accept(object $dispatcher): bool
    {
        return $dispatcher instanceof ClientInterface;
    }

    public function send(object $dispatcher, Message $message): int
    {
        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent'   => Base::getUserAgent(),
        ];

        $request  = new Request(Url::parse($message->getEndpoint()), 'POST', $headers, $message->getPayload());
        $response = $dispatcher->request($request);

        return $response->getStatusCode();
    }
}
