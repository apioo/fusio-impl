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

namespace Fusio\Impl\Backend\Action\Action;

use Doctrine\DBAL\Connection;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service\Action;
use Fusio\Model\Backend\ActionExecuteRequest;
use Fusio\Worker\MessageException;
use PSX\Framework\Exception\Converter;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Response;
use PSX\Http\Writer\WriterInterface;
use stdClass;
use Throwable;

/**
 * Execute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Execute implements ActionInterface
{
    private Converter $exceptionConverter;

    public function __construct(private Action\Executor $actionExecutorService, private Connection $connection)
    {
        $this->exceptionConverter = new Converter(true);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof ActionExecuteRequest);

        $this->connection->beginTransaction();

        try {
            $response = $this->actionExecutorService->execute(
                $request->get('action_id'),
                $body
            );

            if ($response instanceof HttpResponseInterface) {
                $headers = (object) $response->getHeaders();
                $body = $response->getBody();

                if ($body instanceof WriterInterface) {
                    $tempResponse = new Response();
                    $body->writeTo($tempResponse);

                    $headers = (object) $tempResponse->getHeaders();
                    $body = (string) $tempResponse->getBody();
                }

                $return = [
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $headers,
                    'body' => $body,
                ];
            } else {
                $return = [
                    'statusCode' => 200,
                    'headers' => new stdClass(),
                    'body' => $response,
                ];
            }
        } catch (Throwable $e) {
            if ($e instanceof MessageException) {
                $body = $e->getPayload();
            } else {
                $body = $this->exceptionConverter->convert($e);
            }

            $return = [
                'statusCode' => 500,
                'headers' => new stdClass(),
                'body' => $body,
            ];
        }

        $this->connection->rollBack();

        return $return;
    }
}
