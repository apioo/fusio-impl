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

namespace Fusio\Impl\System\Action;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Base;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace;
use Fusio\Impl\Table;
use PSX\Framework\Config\ConfigInterface;
use PSX\Sql\Condition;

/**
 * GetAbout
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GetAbout extends ActionAbstract
{
    private Service\Config $configService;
    private ConfigInterface $config;
    private Table\Category $categoryTable;
    private Table\Scope $scopeTable;
    private Marketplace\Repository\Local $localRepository;

    public function __construct(RuntimeInterface $runtime, Service\Config $configService, ConfigInterface $config, Table\Category $categoryTable, Table\Scope $scopeTable, Marketplace\Repository\Local $localRepository)
    {
        parent::__construct($runtime);

        $this->configService = $configService;
        $this->config = $config;
        $this->categoryTable = $categoryTable;
        $this->scopeTable = $scopeTable;
        $this->localRepository = $localRepository;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return array_filter([
            'apiVersion' => Base::getVersion(),
            'title' => $this->configService->getValue('info_title') ?: 'Fusio',
            'description' => $this->configService->getValue('info_description') ?: null,
            'termsOfService' => $this->configService->getValue('info_tos') ?: null,
            'contactName' => $this->configService->getValue('info_contact_name') ?: null,
            'contactUrl' => $this->configService->getValue('info_contact_url') ?: null,
            'contactEmail' => $this->configService->getValue('info_contact_email') ?: null,
            'licenseName' => $this->configService->getValue('info_license_name') ?: null,
            'licenseUrl' => $this->configService->getValue('info_license_url') ?: null,
            'paymentCurrency' => $this->configService->getValue('payment_currency') ?: 'EUR',
            'categories' => $this->getCategories(),
            'scopes' => $this->getScopes(),
            'apps' => $this->getApps(),
            'links' => $this->getLinks(),
        ]);
    }

    private function getApps(): array
    {
        $appsUrl = $this->config->get('fusio_apps_url');
        $apps = $this->localRepository->fetchAll();

        $result = [];
        foreach ($apps as $app) {
            $result[$app->getName()] = $appsUrl . '/' . $app->getName();
        }

        return $result;
    }

    private function getCategories(): array
    {
        $categories = $this->categoryTable->findAll(null, 0, 1024);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row['name'];
        }

        return $result;
    }

    private function getScopes(): array
    {
        $condition = new Condition();
        $condition->equals('category_id', 1);
        $categories = $this->scopeTable->findAll($condition, 0, 1024, 'name', Sql::SORT_ASC);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row['name'];
        }

        return $result;
    }

    private function getLinks(): array
    {
        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');
        $links = [];

        $links[] = [
            'rel' => 'root',
            'href' => $baseUrl,
        ];

        $links[] = [
            'rel' => 'openapi',
            'href' => $baseUrl . 'system/export/openapi/*/*',
        ];

        $links[] = [
            'rel' => 'documentation',
            'href' => $baseUrl . 'system/doc',
        ];

        $links[] = [
            'rel' => 'route',
            'href' => $baseUrl . 'system/route',
        ];

        $links[] = [
            'rel' => 'health',
            'href' => $baseUrl . 'system/health',
        ];

        $links[] = [
            'rel' => 'jsonrpc',
            'href' => $baseUrl . 'system/jsonrpc',
        ];

        $links[] = [
            'rel' => 'oauth2',
            'href' => $baseUrl . 'authorization/token',
        ];

        $links[] = [
            'rel' => 'whoami',
            'href' => $baseUrl . 'authorization/whoami',
        ];

        $links[] = [
            'rel' => 'about',
            'href' => 'https://www.fusio-project.org',
        ];

        return $links;
    }
}
