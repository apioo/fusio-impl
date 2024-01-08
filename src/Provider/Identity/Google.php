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

namespace Fusio\Impl\Provider\Identity;

use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Identity\ProviderAbstract;
use Fusio\Engine\ParametersInterface;
use PSX\Uri\Uri;

/**
 * Google
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Google extends ProviderAbstract
{
    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newInput('client_id', 'Client-ID', 'text', 'Client-ID'));
        $builder->add($elementFactory->newInput('client_secret', 'Client-Secret', 'text', 'Client-Secret'));
    }

    public function getAuthorizationUri(): ?string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }

    public function getTokenUri(): ?string
    {
        return 'https://oauth2.googleapis.com/token';
    }

    public function getUserInfoUri(): ?string
    {
        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }

    public function getRedirectUri(ParametersInterface $configuration, string $state, string $redirectUri): Uri
    {
        $uri = parent::getRedirectUri($configuration, $state, $redirectUri);

        $parameters = $uri->getParameters();
        $parameters['scope'] = 'openid%20profile%20email';

        return $uri->withParameters($parameters);
    }

    protected function getIdProperty(): string
    {
        return 'sub';
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
