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
use Fusio\Impl\Base;
use Fusio\Impl\Service;
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

    public function __construct(Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig, Table\Category $categoryTable, Table\Scope $scopeTable)
    {
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
        $this->categoryTable = $categoryTable;
        $this->scopeTable = $scopeTable;
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
            'categories' => $this->getCategories($context),
            'scopes' => $this->getScopes($context),
            'links' => $this->getLinks(),
        ]);
    }

    private function getCategories(ContextInterface $context): array
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\CategoryTable::COLUMN_TENANT_ID, $context->getTenantId());
        $categories = $this->categoryTable->findAll($condition, 0, 1024, Table\Generated\CategoryTable::COLUMN_NAME, OrderBy::ASC);

        $result = [];
        foreach ($categories as $row) {
            $result[] = $row->getName();
        }

        return $result;
    }

    private function getScopes(ContextInterface $context): array
    {
        $defaultCategory = $this->categoryTable->findOneByTenantAndName($context->getTenantId(), 'default');
        if (!$defaultCategory instanceof Table\Generated\CategoryRow) {
            return [];
        }

        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\ScopeTable::COLUMN_TENANT_ID, $context->getTenantId());
        $condition->equals(Table\Generated\ScopeTable::COLUMN_CATEGORY_ID, $defaultCategory->getId());
        $scopes = $this->scopeTable->findAll($condition, 0, 1024, Table\Generated\ScopeTable::COLUMN_NAME, OrderBy::ASC);

        $result = [];
        foreach ($scopes as $row) {
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
            'rel' => 'oauth-authorization-server',
            'href' => $baseUrl . 'system/oauth-authorization-server',
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
