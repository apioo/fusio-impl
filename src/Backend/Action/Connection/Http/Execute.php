<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Backend\Action\Connection\Http;

use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Model\Backend\HttpRequest;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;
use PSX\Http\Environment\HttpResponse;
use PSX\Http\Exception\BadRequestException;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Execute extends HttpAbstract
{
    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $connection = $this->getConnection($request);
        $payload = $request->getPayload();

        assert($payload instanceof HttpRequest);

        $method = $payload->getMethod() ?? throw new BadRequestException('Provided no HTTP method');
        $uri = $payload->getUri() ?? throw new BadRequestException('Provided no URI');
        $headers = $payload->getHeaders()?->getAll() ?? [];
        $body = $payload->getBody();

        $httpRequest = new Request($method, $uri, $headers, $body);
        $httpResponse = $connection->send($httpRequest);

        return new HttpResponse(200, [], [
            'statusCode' => $httpResponse->getStatusCode(),
            'headers' => $this->convertHeaders($httpResponse),
            'body' => (string) $httpResponse->getBody(),
        ]);
    }

    private function convertHeaders(ResponseInterface $response): array
    {
        $result = [];
        foreach ($response->getHeaders() as $name => $values) {
            $result[$name] = implode(',', $values);
        }

        return $result;
    }
}
