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

namespace Fusio\Impl\Backend\Action\Action;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Action;
use Fusio\Model\Backend\ActionExecuteRequest;
use Fusio\Worker\MessageException;
use PSX\Framework\Exception\Converter;
use PSX\Http\Environment\HttpResponseInterface;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Execute implements ActionInterface
{
    private Action\Executor $actionExecutorService;
    private Converter $exceptionConverter;

    public function __construct(Action\Executor $actionExecutorService)
    {
        $this->actionExecutorService = $actionExecutorService;
        $this->exceptionConverter = new Converter(true);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof ActionExecuteRequest);

        try {
            $response = $this->actionExecutorService->execute(
                $request->get('action_id'),
                $body
            );

            if ($response instanceof HttpResponseInterface) {
                return [
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $response->getHeaders() ?: new \stdClass(),
                    'body' => $response->getBody(),
                ];
            } else {
                return [
                    'statusCode' => 200,
                    'headers' => new \stdClass(),
                    'body' => $response,
                ];
            }
        } catch (\Throwable $e) {
            if ($e instanceof MessageException) {
                $body = $e->getPayload();
            } else {
                $body = $this->exceptionConverter->convert($e);
            }

            return [
                'statusCode' => 500,
                'headers' => new \stdClass(),
                'body' => $body,
            ];
        }
    }
}
