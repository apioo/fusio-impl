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

namespace Fusio\Impl\Console;

use Composer\InstalledVersions;
use Fusio\Cli\Transport\TransportInterface;
use PSX\Framework\Dispatch\Dispatch;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Request;
use PSX\Http\Response;
use PSX\Http\Stream\Stream;
use PSX\Json\Parser;
use PSX\Uri\Uri;

/**
 * Internal transport for the commands so that we dont need to send an actual HTTP request
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Transport implements TransportInterface
{
    /**
     * @var Dispatch
     */
    private $dispatch;

    public function __construct(Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    public function request(string $baseUri, string $method, string $path, ?array $query = null, ?array $headers = null, $body = null): HttpResponseInterface
    {
        $uri = new Uri('/' . $path);
        if ($query !== null) {
            $uri = $uri->withParameters($query);
        }

        $headers['User-Agent'] = 'Fusio CLI v' . InstalledVersions::getPrettyVersion('fusio/cli');

        if ($body instanceof \JsonSerializable) {
            $headers['Content-Type'] = 'application/json';
            $body = Parser::encode($body);
        } elseif (is_string($body)) {
            $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        }

        $request  = new Request($uri, $method, $headers, $body);
        $response = new Response();
        $response->setBody(new Stream(fopen('php://memory', 'r+')));

        $this->dispatch->route($request, $response);

        return new HttpResponse(
            $response->getStatusCode(),
            $response->getHeaders(),
            $response->getBody()
        );
    }
}
