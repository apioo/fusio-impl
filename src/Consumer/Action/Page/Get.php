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

namespace Fusio\Impl\Consumer\Action\Page;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Consumer\View;
use PSX\Framework\Config\ConfigInterface;
use PSX\Sql\TableManagerInterface;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Get implements ActionInterface
{
    private View\Page $view;
    private ConfigInterface $config;

    public function __construct(View\Page $view, ConfigInterface $config)
    {
        $this->view = $view;
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $entity = $this->view->getEntity(
            $request->get('page_id'),
            $context->getTenantId()
        );

        $entity['content'] = $this->replaceVariables($entity['content']);

        return $entity;
    }

    private function replaceVariables(string $content): string
    {
        $baseUrl = $this->config->get('psx_url');
        $apiUrl = $baseUrl . '/' . $this->config->get('psx_dispatch');
        $url = $this->config->get('fusio_apps_url');
        $basePath = parse_url($url, PHP_URL_PATH);

        $env = [
            'API_URL' => $apiUrl,
            'APPS_URL' => $url,
            'BASE_URL' => $baseUrl,
            'BASE_PATH' => $basePath,
        ];

        foreach ($env as $key => $value) {
            $content = str_replace(['{' . $key . '}'], [$value], $content);
        }

        return $content;
    }
}
