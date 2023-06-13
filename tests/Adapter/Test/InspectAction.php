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

namespace Fusio\Impl\Tests\Adapter\Test;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequestContext;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Worker\Generated\RpcRequest;
use PSX\Http\Exception as StatusCode;

/**
 * InspectAction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class InspectAction extends ActionAbstract
{
    public function getName(): string
    {
        return 'Inspect-Action';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $throw = $request->get('throw');
        if ($throw) {
            throw new StatusCode\InternalServerErrorException('Foobar');
        }

        $data = [];
        $context = $request->getContext();
        if ($context instanceof HttpRequestContext) {
            $data = [
                'method' => $context->getRequest()->getMethod(),
                'headers' => $context->getRequest()->getHeaders(),
                'uri_fragments' => $context->getParameters(),
                'parameters' => $context->getRequest()->getUri()->getParameters(),
            ];
        } elseif ($context instanceof RpcRequest) {
            $data = [
                'name' => $context->getName(),
            ];
        }

        return $this->response->build(200, [], [
            'arguments' => $request->getArguments(),
            'payload' => $request->getPayload(),
            'context' => $data
        ]);
    }
}
