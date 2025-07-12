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

namespace Fusio\Impl\Framework\Api\Configurator;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\ConfiguratorInterface;
use PSX\Api\Generator;
use PSX\Api\Scanner\FilterInterface;

/**
 * OpenAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class OpenAPI implements ConfiguratorInterface
{
    private Table\Scope $scopeTable;
    private Service\Config $configService;
    private Service\System\FrameworkConfig $frameworkConfig;

    public function __construct(Table\Scope $scopeTable, Service\Config $configService, Service\System\FrameworkConfig $frameworkConfig)
    {
        $this->scopeTable = $scopeTable;
        $this->configService = $configService;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function accept(object $generator): bool
    {
        return $generator instanceof Generator\Spec\OpenAPI;
    }

    public function configure(object $generator, ?FilterInterface $filter = null): void
    {
        if (!$generator instanceof Generator\Spec\OpenAPI) {
            throw new \InvalidArgumentException('Provided an invalid generator');
        }

        $generator->setTitle($this->configService->getValue('info_title') ?: 'Fusio');
        $generator->setDescription($this->configService->getValue('info_description') ?: null);
        $generator->setTermsOfService($this->configService->getValue('info_tos') ?: null);
        $generator->setContactName($this->configService->getValue('info_contact_name') ?: null);
        $generator->setContactUrl($this->configService->getValue('info_contact_url') ?: null);
        $generator->setContactEmail($this->configService->getValue('info_contact_email') ?: null);
        $generator->setLicenseName($this->configService->getValue('info_license_name') ?: null);
        $generator->setLicenseUrl($this->configService->getValue('info_license_url') ?: null);

        $filterId = $filter !== null ? (int) $filter->getId() : 1;
        $scopes = $this->scopeTable->getAvailableScopes($filterId, $this->frameworkConfig->getTenantId());
        $authorizationUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'authorize');
        $tokenUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'token');
        $refreshUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'token');

        $generator->setAuthorizationFlow('app', Generator\Spec\ApiAbstract::FLOW_CLIENT_CREDENTIALS, $authorizationUrl, $tokenUrl, $refreshUrl, $scopes);
    }
}
