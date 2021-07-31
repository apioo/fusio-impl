<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Worker;

use Fusio\Impl\Worker\Generated\WorkerClient;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;

/**
 * ClientFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ClientFactory
{
    private static $instances = [];

    public static function getClient(string $endpoint): WorkerClient
    {
        if (isset(self::$instances[$endpoint])) {
            return self::$instances[$endpoint];
        }

        [$host, $port] = explode(':', $endpoint);
        $socket = new TSocket($host, (int) $port);
        $transport = new TBufferedTransport($socket, 1024, 1024);
        $protocol = new TBinaryProtocol($transport);
        $transport->open();

        return self::$instances[$endpoint] = new WorkerClient($protocol);
    }
}