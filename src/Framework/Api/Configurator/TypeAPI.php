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

use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Api\ConfiguratorInterface;
use PSX\Api\Generator;
use PSX\Api\Scanner\FilterInterface;
use PSX\Api\Security\OAuth2;

/**
 * TypeAPI
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TypeAPI implements ConfiguratorInterface
{
    private Table\Scope $scopeTable;
    private FrameworkConfig $frameworkConfig;

    public function __construct(Table\Scope $scopeTable, FrameworkConfig $frameworkConfig)
    {
        $this->scopeTable = $scopeTable;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function accept(object $generator): bool
    {
        return $generator instanceof Generator\Spec\TypeAPI;
    }

    public function configure(object $generator, ?FilterInterface $filter = null): void
    {
        if (!$generator instanceof Generator\Spec\TypeAPI) {
            throw new \InvalidArgumentException('Provided an invalid generator');
        }

        $baseUrl = $this->frameworkConfig->getDispatchUrl();
        $authorizationUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'authorize');
        $tokenUrl = $this->frameworkConfig->getDispatchUrl('authorization', 'token');
        $filterId = $filter !== null ? (int) $filter->getId() : 1;
        $scopes = $this->scopeTable->getAvailableScopes($filterId, $this->frameworkConfig->getTenantId());

        $generator->setBaseUrl($baseUrl);
        $generator->setSecurity(new OAuth2($tokenUrl, $authorizationUrl, array_keys($scopes)));
    }
}
