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

namespace Fusio\Impl\Provider\Identity;

use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Identity\ProviderAbstract;
use Fusio\Impl\Service\System\FrameworkConfig;
use PSX\Http\Client\ClientInterface;

/**
 * Fusio
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Fusio extends ProviderAbstract
{
    public function __construct(private FrameworkConfig $frameworkConfig, ClientInterface $httpClient)
    {
        parent::__construct($httpClient);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
    }

    public function getAuthorizationUri(): ?string
    {
        return $this->frameworkConfig->getDispatchUrl('authorization', 'authorize');
    }

    public function getTokenUri(): ?string
    {
        return $this->frameworkConfig->getDispatchUrl('authorization', 'token');
    }

    public function getUserInfoUri(): ?string
    {
        return $this->frameworkConfig->getDispatchUrl('authorization', 'token');
    }

    protected function getIdProperty(): string
    {
        return 'id';
    }

    protected function getNameProperty(): string
    {
        return 'name';
    }

    protected function getEmailProperty(): string
    {
        return 'email';
    }
}
