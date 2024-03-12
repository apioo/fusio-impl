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

namespace Fusio\Impl\System\Action\Meta;

use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Base;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace;
use Fusio\Impl\Table;
use PSX\Sql\Condition;
use PSX\Sql\OrderBy;

/**
 * GetAbout
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetAbout implements ActionInterface
{
    private Service\Config $configService;
    private Service\System\FrameworkConfig $frameworkConfig;
    private Table\Category $categoryTable;
    private Table\Scope $scopeTable;
    private Marketplace\Repository\Local $localRepository;

    public function __construct(Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig, Table\Category $categoryTable, Table\Scope $scopeTable, Marketplace\Repository\Local $localRepository)
    {
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
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
        $appsUrl = $this->frameworkConfig->getAppsUrl();
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
            $result[] = $row->getName();
        }

        return $result;
    }

    private function getScopes(): array
    {
        $condition = Condition::withAnd();
        $condition->equals('category_id', 1);
        $categories = $this->scopeTable->findAll($condition, 0, 1024, 'name', OrderBy::ASC);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row->getName();
        }

        return $result;
    }

    private function getLinks(): array
    {
        $baseUrl = $this->frameworkConfig->getDispatchUrl();
        $links = [];

        $links[] = [
            'rel' => 'root',
            'href' => $baseUrl,
        ];

        $links[] = [
            'rel' => 'openapi',
            'href' => $baseUrl . 'system/generator/spec-openapi',
        ];

        $links[] = [
            'rel' => 'typeapi',
            'href' => $baseUrl . 'system/generator/spec-typeapi',
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
