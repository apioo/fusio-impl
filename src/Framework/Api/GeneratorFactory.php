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

namespace Fusio\Impl\Framework\Api;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Provider\Generator\OpenAPI;
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
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GeneratorFactory extends ApiGeneratorFactory
{
    private Table\Scope $scopeTable;
    private Service\Config $configService;

    public function __construct(Table\Scope $scopeTable, Service\Config $configService, string $namespace, string $url, string $dispatch)
    {
        parent::__construct($namespace, $url, $dispatch);

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
