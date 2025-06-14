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

namespace Fusio\Impl\System\Action\Meta;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Service;
use PSX\Http\Environment\HttpResponse;

/**
 * GetAPICatalog
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class GetAPICatalog implements ActionInterface
{
    public function __construct(private Service\System\FrameworkConfig $frameworkConfig)
    {
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return new HttpResponse(200, [
            'Content-Type' => 'application/linkset+json; profile="https://www.rfc-editor.org/info/rfc9727"',
        ], [
            'linkset' => [
                $this->buildLinkSet()
            ],
        ]);
    }

    private function buildLinkSet(): array
    {
        $baseUrl = $this->frameworkConfig->getDispatchUrl();
        $appsUrl = $this->frameworkConfig->getAppsUrl();

        $linkSet = [];
        $linkSet['anchor'] = $baseUrl;
        $linkSet['service-desc'] = [
            'href' => $baseUrl . 'system/generator/spec-openapi',
            'type' => 'application/json',
        ];
        $linkSet['service-doc'] = [
            'href' => $appsUrl . '/redoc',
            'type' => 'text/html',
        ];
        $linkSet['service-meta'] = [
            'href' => $baseUrl . 'system/health',
            'type' => 'application/json',
        ];
        $linkSet['status'] = [
            'href' => $baseUrl . 'system/health',
            'type' => 'application/json',
        ];

        return $linkSet;
    }
}
