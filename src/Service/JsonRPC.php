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

namespace Fusio\Impl\Service;

use Fusio\Impl\Framework\Loader\ContextFactory;
use Fusio\Model;
use InvalidArgumentException;
use PSX\Http\MediaType;
use PSX\Http\Response;
use PSX\Json\Rpc\Context as RpcContext;
use PSX\Json\Rpc\Exception\InvalidRequestException;
use PSX\Record\Record;
use stdClass;

/**
 * JsonRPC
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class JsonRPC
{
    public const CONTEXT_AUTHORIZATION = 'authorization';
    public const CONTEXT_IP = 'ip';

    public function __construct(
        private JsonRPC\Invoker $invoker,
        private ContextFactory $contextFactory,
    ) {
    }

    public function __invoke(string $method, array|stdClass|null $params, RpcContext $rpcContext): mixed
    {
        if (is_array($params)) {
            throw new InvalidRequestException('Params as array (by-position) are not supported, please use params as object (by-name)');
        }

        if ($params instanceof stdClass) {
            $arguments = Record::fromObject($params);
        } else {
            $arguments = new Record();
        }

        $context = $this->contextFactory->getActive();

        $response = $this->invoker->invoke(
            $method,
            $arguments,
            $rpcContext->get(self::CONTEXT_AUTHORIZATION),
            $rpcContext->get(self::CONTEXT_IP),
            $context
        );

        if ($this->isJson($response)) {
            // in case the response contains JSON data we decode it
            return json_decode((string) $response->getBody());
        } else {
            return $response->getBody();
        }
    }

    private function isJson(Response $response): bool
    {
        try {
            return MediaType\Json::isMediaType(MediaType::parse($response->getHeader('Content-Type')));
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
