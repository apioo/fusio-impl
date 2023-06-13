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

namespace Fusio\Impl\Framework\Api;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\Generator;
use PSX\Api\GeneratorFactory as ApiGeneratorFactory;
use PSX\Api\GeneratorInterface;
use PSX\Api\Scanner\FilterInterface;

/**
 * GeneratorFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GeneratorFactory extends ApiGeneratorFactory
{
    private Table\Scope $scopeTable;
    private Service\Config $configService;

    public function __construct(Table\Scope $scopeTable, Service\Config $configService, string $url, string $dispatch)
    {
        parent::__construct($url, $dispatch);

        $this->scopeTable    = $scopeTable;
        $this->configService = $configService;
    }

    protected function configure(GeneratorInterface $generator, ?FilterInterface $filter = null): void
    {
        /*
        if ($generator instanceof OpenAPI) {
            $generator->setTitle($this->configService->getValue('info_title') ?: 'Fusio');
            $generator->setDescription($this->configService->getValue('info_description') ?: null);
            $generator->setTermsOfService($this->configService->getValue('info_tos') ?: null);
            $generator->setContactName($this->configService->getValue('info_contact_name') ?: null);
            $generator->setContactUrl($this->configService->getValue('info_contact_url') ?: null);
            $generator->setContactEmail($this->configService->getValue('info_contact_email') ?: null);
            $generator->setLicenseName($this->configService->getValue('info_license_name') ?: null);
            $generator->setLicenseUrl($this->configService->getValue('info_license_url') ?: null);

            $scopes     = $this->scopeTable->getAvailableScopes($filter !== null ? (int) $filter->getId() : 1);
            $tokenUrl   = $this->url . '/' . $this->dispatch . 'authorization/token';
            $refreshUrl = $this->url . '/' . $this->dispatch . 'authorization/token';

            $generator->setAuthorizationFlow(Authorization::APP, Generator\Spec\OpenAPIAbstract::FLOW_CLIENT_CREDENTIALS, null, $tokenUrl, $refreshUrl, $scopes);
        } elseif ($generator instanceof Generator\Spec\Raml) {
            $generator->setTitle($this->configService->getValue('info_title') ?: 'Fusio');
        }
        */
    }
}
