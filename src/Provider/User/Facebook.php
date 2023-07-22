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

namespace Fusio\Impl\Provider\User;

use Fusio\Engine\User\ConfigurationInterface;
use Fusio\Engine\User\ProviderAbstract;

/**
 * Facebook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Facebook extends ProviderAbstract
{
    public function getAuthorizationUri(): ?string
    {
        return 'https://www.facebook.com/v17.0/dialog/oauth';
    }

    public function getTokenUri(): ?string
    {
        return 'https://graph.facebook.com/v17.0/oauth/access_token';
    }

    public function getUserInfoUri(): ?string
    {
        return 'https://graph.facebook.com/v2.5/me';
    }

    protected function getUserInfoParameters(ConfigurationInterface $configuration): array
    {
        return [
            'fields' => 'id,name,email'
        ];
    }
}
