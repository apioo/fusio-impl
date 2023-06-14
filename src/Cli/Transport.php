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

namespace Fusio\Impl\Cli;

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
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Transport implements TransportInterface
{
    private Dispatch $dispatch;

    public function __construct(Dispatch $dispatch)
    {
        $this->dispatch = $dispatch;
    }

    public function request(string $baseUri, string $method, string $path, ?array $query = null, ?array $headers = null, $body = null): HttpResponseInterface
    {
        $uri = Uri::parse('/' . $path);
        if ($query !== null) {
            $uri = $uri->withParameters($query);
        }

        $headers['User-Agent'] = 'Fusio CLI ' . InstalledVersions::getPrettyVersion('fusio/cli');

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
