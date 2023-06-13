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

namespace Fusio\Impl\Service\Action;

use Fusio\Engine\Context;
use Fusio\Engine\ProcessorInterface;
use Fusio\Engine\Repository;
use Fusio\Engine\Request;
use Fusio\Model\Backend\ActionExecuteRequest;
use PSX\Http\Request as HttpRequest;
use PSX\Record\Record;
use PSX\Uri\Uri;

/**
 * Executor
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Executor
{
    private ProcessorInterface $processor;
    private Repository\AppInterface $appRepository;
    private Repository\UserInterface $userRepository;

    public function __construct(ProcessorInterface $processor, Repository\AppInterface $appRepository, Repository\UserInterface $userRepository)
    {
        $this->processor      = $processor;
        $this->appRepository  = $appRepository;
        $this->userRepository = $userRepository;
    }

    public function execute(string|int $actionId, ActionExecuteRequest $request): mixed
    {
        $body = $request->getBody();
        if ($body === null) {
            $body = new Record();
        }

        $app  = $this->appRepository->get(1) ?? throw new \RuntimeException('App 1 not available');
        $user = $this->userRepository->get(1) ?? throw new \RuntimeException('User 1 not available');

        $uriFragments = $this->parseQueryString($request->getUriFragments());
        $parameters   = $this->parseQueryString($request->getParameters());
        $headers      = $this->parseQueryString($request->getHeaders());

        $uri = Uri::parse('/');
        $uri = $uri->withParameters($parameters);

        $httpRequest = new HttpRequest($uri, $request->getMethod() ?? 'GET', $headers);

        $arguments = [];
        $arguments = array_merge($arguments, $parameters);
        $arguments = array_merge($arguments, $uriFragments);

        $request = new Request($arguments, $body, new Request\HttpRequestContext($httpRequest, $uriFragments));
        $context = new Context(0, '/', $app, $user);

        return $this->processor->execute($actionId, $request, $context);
    }

    private function parseQueryString(?string $data): array
    {
        $result = array();
        if (!empty($data)) {
            parse_str($data, $result);
        }
        return $result;
    }
}
