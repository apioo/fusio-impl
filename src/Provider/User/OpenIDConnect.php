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

use Fusio\Engine\User\UserDetails;
use PSX\Uri\Uri;

/**
 * OpenID Connect
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class OpenIDConnect extends ProviderAbstract
{
    public function getRedirectUri(Uri $uri): Uri
    {
        $parameters = $uri->getParameters();
        $parameters['scope'] = 'openid';

        return $uri->withParameters($parameters);
    }

    public function requestUser(ConfigurationInterface $configuration, string $code, string $clientId, string $redirectUri): ?UserDetails
    {
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $configuration->getClientSecret(),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $idToken = $this->obtainIDToken($configuration->getTokenUri(), $params);
        if (empty($idToken)) {
            return null;
        }

        $data = JWT::decode($idToken);

        // try standard claims
        // s. https://openid.net/specs/openid-connect-core-1_0.html#StandardClaims
        $id    = $data['sub'] ?? null;
        $name  = $data['preferred_username'] ?? null;
        $email = $data['email'] ?? null;

        if (!empty($id) && !empty($name)) {
            return new UserDetails($id, $name, $email);
        } else {
            return null;
        }
    }

}