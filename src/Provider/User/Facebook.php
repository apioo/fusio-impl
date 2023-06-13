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

/**
 * Facebook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Facebook extends ProviderAbstract
{
    public function getId(): int
    {
        return self::PROVIDER_FACEBOOK;
    }

    public function requestUser(string $code, string $clientId, string $redirectUri): ?UserDetails
    {
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $this->getSecret(),
            'redirect_uri'  => $redirectUri,
        ];

        $accessToken = $this->obtainAccessToken('https://graph.facebook.com/v12.0/oauth/access_token', $params, self::TYPE_GET);
        if (empty($accessToken)) {
            return null;
        }

        $data = $this->obtainUserInfo('https://graph.facebook.com/v2.5/me', $accessToken, ['access_token' => $accessToken, 'fields' => 'id,name,email']);
        if (empty($data)) {
            return null;
        }

        $id    = $data->id ?? null;
        $name  = $data->name ?? null;
        $email = $data->email ?? null;

        if (!empty($id) && !empty($name)) {
            return new UserDetails($id, $name, $email);
        } else {
            return null;
        }
    }
}
