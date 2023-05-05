<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Impl\Consumer\Action\Page;

use Fusio\Engine\ActionAbstract;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Get extends ActionAbstract
{
    private View\Page $table;
    private ConfigInterface $config;

    public function __construct(TableManagerInterface $tableManager, ConfigInterface $config)
    {
        $this->table = $tableManager->getTable(View\Page::class);
        $this->config = $config;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $entity = $this->table->getEntity(
            $request->get('page_id')
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
