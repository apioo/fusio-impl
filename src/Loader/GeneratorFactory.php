<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Loader;

use Doctrine\Common\Annotations\Reader;
use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Api\Generator;
use PSX\Api\GeneratorInterface;

/**
 * GeneratorFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GeneratorFactory extends \PSX\Api\GeneratorFactory
{
    /**
     * @var \Fusio\Impl\Table\Scope
     */
    protected $scopeTable;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    public function __construct(Table\Scope $scopeTable, Service\Config $configService, Reader $reader, $namespace, $url, $dispatch)
    {
        parent::__construct($reader, $namespace, $url, $dispatch);

        $this->scopeTable    = $scopeTable;
        $this->configService = $configService;
    }

    protected function configure(GeneratorInterface $generator)
    {
        if ($generator instanceof Generator\Spec\OpenAPIAbstract) {
            $refreshUrl = $this->url . '/' . $this->dispatch . 'authorization/token';

            $generator->setTitle($this->configService->getValue('info_title') ?: 'Fusio');
            $generator->setDescription($this->configService->getValue('info_description') ?: null);
            $generator->setTermsOfService($this->configService->getValue('info_tos') ?: null);
            $generator->setContactName($this->configService->getValue('info_contact_name') ?: null);
            $generator->setContactUrl($this->configService->getValue('info_contact_url') ?: null);
            $generator->setContactEmail($this->configService->getValue('info_contact_email') ?: null);
            $generator->setLicenseName($this->configService->getValue('info_license_name') ?: null);
            $generator->setLicenseUrl($this->configService->getValue('info_license_url') ?: null);

            $appScopes = $this->scopeTable->getScopesForType(Table\Scope::TYPE_APP);
            $backendScopes = $this->scopeTable->getScopesForType(Table\Scope::TYPE_BACKEND);
            $consumerScopes = $this->scopeTable->getScopesForType(Table\Scope::TYPE_CONSUMER);

            $authUrl  = $this->configService->getValue('authorization_url') ?: $this->url . '/developer/auth';
            $tokenUrl = $this->url . '/' . $this->dispatch . 'authorization/token';

            $generator->setAuthorizationFlow(Authorization::APP, Generator\Spec\OpenAPIAbstract::FLOW_AUTHORIZATION_CODE, $authUrl, $tokenUrl, $refreshUrl, $appScopes);
            $generator->setAuthorizationFlow(Authorization::APP, Generator\Spec\OpenAPIAbstract::FLOW_CLIENT_CREDENTIALS, null, $tokenUrl, $refreshUrl, $appScopes);
            $generator->setAuthorizationFlow(Authorization::APP, Generator\Spec\OpenAPIAbstract::FLOW_PASSWORD, null, $tokenUrl, $refreshUrl, $appScopes);

            $tokenUrl = $this->url . '/' . $this->dispatch . 'backend/token';
            $generator->setAuthorizationFlow(Authorization::BACKEND, Generator\Spec\OpenAPIAbstract::FLOW_CLIENT_CREDENTIALS, null, $tokenUrl, $refreshUrl, $backendScopes);

            $tokenUrl = $this->url . '/' . $this->dispatch . 'consumer/token';
            $generator->setAuthorizationFlow(Authorization::CONSUMER, Generator\Spec\OpenAPIAbstract::FLOW_CLIENT_CREDENTIALS, null, $tokenUrl, $refreshUrl, $consumerScopes);
        } elseif ($generator instanceof Generator\Spec\Raml) {
            $generator->setTitle($this->configService->getValue('info_title') ?: 'Fusio');
        }
    }
}
