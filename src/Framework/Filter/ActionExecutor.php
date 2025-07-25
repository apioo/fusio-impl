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

namespace Fusio\Impl\Framework\Filter;

use Fusio\Engine\Request;
use Fusio\Impl\Controller\ActionController;
use Fusio\Impl\Framework\Loader\Context;
use PSX\Data\Body;
use PSX\Data\ReaderInterface;
use PSX\Data\Transformer\Noop;
use PSX\Framework\Http\RequestReader;
use PSX\Framework\Http\ResponseWriter;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Record\Record;
use PSX\Schema\SchemaManagerInterface;

/**
 * ControllerExecutor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ActionExecutor implements FilterInterface
{
    private ActionController $controller;
    private Context $context;
    private SchemaManagerInterface $schemaManager;
    private RequestReader $requestReader;
    private ResponseWriter $responseWriter;

    public function __construct(ActionController $controller, Context $context, SchemaManagerInterface $schemaManager, RequestReader $requestReader, ResponseWriter $responseWriter)
    {
        $this->controller = $controller;
        $this->context = $context;
        $this->schemaManager = $schemaManager;
        $this->requestReader = $requestReader;
        $this->responseWriter = $responseWriter;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain): void
    {
        $arguments = array_merge($request->getUri()->getParameters(), $this->context->getParameters());

        $requestContext = new Request\HttpRequestContext($request, $this->context->getParameters());

        $incoming = $this->context->getOperation()->getIncoming();
        if (!empty($incoming) && in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'])) {
            if ($incoming === 'schema://Passthru') {
                $payload = $this->requestReader->getBody($request);
            } elseif ($incoming === 'mime://application/octet-stream') {
                $payload = $request->getBody();
            } elseif ($incoming === 'mime://application/x-www-form-urlencoded') {
                $payload = Body\Form::from($this->requestReader->getBody($request, ReaderInterface::FORM));
            } elseif ($incoming === 'mime://application/json') {
                $payload = Body\Json::from($this->requestReader->getBody($request, ReaderInterface::JSON));
            } elseif ($incoming === 'mime://multipart/form-data') {
                $payload = $this->requestReader->getBody($request, ReaderInterface::MULTIPART);
            } elseif ($incoming === 'mime://text/plain') {
                $payload = (string) $request->getBody();
            } elseif ($incoming === 'mime://application/xml') {
                $payload = $this->requestReader->getBody($request, ReaderInterface::XML, new Noop());
            } else {
                $schema  = $this->schemaManager->getSchema($incoming);
                $payload = $this->requestReader->getBodyAs($request, $schema);
            }
        } else {
            $payload = new Record();
        }

        $result = $this->controller->execute(new Request($arguments, $payload, $requestContext), $this->context);

        $this->responseWriter->setBody($response, $result, $request);

        $filterChain->handle($request, $response);
    }
}
