<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\MessengerHandler;

use Fusio\Impl\Base;
use Fusio\Impl\Messenger\SendHttpRequest;
use Fusio\Impl\Table;
use PSX\DateTime\LocalDateTime;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Request;
use PSX\Json\Parser;
use PSX\Uri\Url;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * SendHttpRequestHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
#[AsMessageHandler]
class SendHttpRequestHandler
{
    public const MAX_ATTEMPTS = 3;

    private Table\Webhook\Response $responseTable;
    private ClientInterface $httpClient;

    public function __construct(Table\Webhook\Response $responseTable, ClientInterface $httpClient)
    {
        $this->responseTable = $responseTable;
        $this->httpClient = $httpClient;
    }

    public function __invoke(SendHttpRequest $httpRequest): void
    {
        $existing = $this->responseTable->find($httpRequest->getResponseId());
        if (!$existing instanceof Table\Generated\WebhookResponseRow) {
            return;
        }

        if ($existing->getStatus() !== Table\Webhook\Response::STATUS_PENDING) {
            return;
        }

        $headers = [
            'Content-Type' => 'application/json',
            'User-Agent' => Base::getUserAgent(),
        ];

        $request  = new Request(Url::parse($httpRequest->getEndpoint()), 'POST', $headers, Parser::encode($httpRequest->getPayload()));
        $response = $this->httpClient->request($request);

        $code     = $response->getStatusCode();
        $attempts = $existing->getAttempts() + 1;

        if (($code >= 200 && $code < 400) || $code == 410) {
            $status = Table\Webhook\Response::STATUS_DONE;
        } else {
            $status = Table\Webhook\Response::STATUS_PENDING;
        }

        // mark response as exceeded in case max attempts is reached
        if ($attempts >= self::MAX_ATTEMPTS) {
            $status = Table\Webhook\Response::STATUS_EXCEEDED;
        }

        $existing->setStatus($status);
        $existing->setAttempts($attempts);
        $existing->setCode($code);
        $existing->setBody((string) $response->getBody());
        $existing->setExecuteDate(LocalDateTime::now());
        $this->responseTable->update($existing);

        if ($status === Table\Webhook\Response::STATUS_PENDING) {
            throw new \RuntimeException('Request is still pending');
        }
    }
}
