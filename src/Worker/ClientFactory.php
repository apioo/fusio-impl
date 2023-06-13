<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Worker;

use Fusio\Impl\Worker\Generated\WorkerClient;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TSocket;

/**
 * ClientFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ClientFactory
{
    private static array $instances = [];

    public static function getClient(string $endpoint, ?string $type = null): WorkerClient
    {
        if (isset(self::$instances[$endpoint])) {
            return self::$instances[$endpoint];
        }

        [$host, $port] = explode(':', $endpoint);

        if ($type === 'php') {
            $socket = new THttpClient($host, (int) $port);
        } else {
            $socket = new TSocket($host, (int) $port);
        }

        $transport = new TBufferedTransport($socket, 1024, 1024);
        $protocol = new TBinaryProtocol($transport);
        $transport->open();

        return self::$instances[$endpoint] = new WorkerClient($protocol);
    }
}