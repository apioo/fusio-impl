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

namespace Fusio\Impl\Provider\User;

use Fusio\Engine\User\UserDetails;

/**
 * Google
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Google extends ProviderAbstract
{
    public function getId(): int
    {
        return self::PROVIDER_GOOGLE;
    }

    public function requestUser(string $code, string $clientId, string $redirectUri): ?UserDetails
    {
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $this->getSecret(),
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $accessToken = $this->obtainAccessToken('https://oauth2.googleapis.com/token', $params);
        if (empty($accessToken)) {
            return null;
        }

        $data = $this->obtainUserInfo('https://www.googleapis.com/userinfo/v2/me', $accessToken);
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
