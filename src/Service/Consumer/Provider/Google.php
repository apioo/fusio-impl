<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Consumer\Provider;

use Fusio\Impl\Service\Consumer\Model\User;
use Fusio\Impl\Service\Consumer\ProviderAbstract;
use PSX\Http\GetRequest;
use PSX\Http\PostRequest;
use PSX\Json\Parser;
use PSX\Uri\Url;
use RuntimeException;

/**
 * Google
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Google extends ProviderAbstract
{
    public function getId()
    {
        return self::PROVIDER_GOOGLE;
    }

    public function requestUser($code, $clientId, $redirectUri)
    {
        $accessToken = $this->getAccessToken($code, $clientId, $this->secret, $redirectUri);

        if (!empty($accessToken)) {
            $url      = new Url('https://www.googleapis.com/plus/v1/people/me/openIdConnect');
            $headers  = ['Authorization' => 'Bearer ' . $accessToken, 'User-Agent' => $this->ua];
            $response = $this->httpClient->request(new GetRequest($url, $headers));

            if ($response->getStatusCode() == 200) {
                $data  = Parser::decode($response->getBody());
                $id    = isset($data->sub) ? $data->sub : null;
                $name  = isset($data->name) ? $data->name : null;
                $email = isset($data->email) ? $data->email : null;

                if (!empty($id) && !empty($name)) {
                    return new User($id, $name, $email);
                }
            }
        }

        return null;
    }

    protected function getAccessToken($code, $clientId, $clientSecret, $redirectUri)
    {
        if (empty($clientSecret)) {
            throw new RuntimeException('No secret provided');
        }

        $url = new Url('https://accounts.google.com/o/oauth2/token');

        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
            'grant_type'    => 'authorization_code'
        ];

        $response = $this->httpClient->request(new PostRequest($url, ['Accept' => 'application/json', 'User-Agent' => $this->ua], $params));

        if ($response->getStatusCode() == 200) {
            $data = Parser::decode($response->getBody());
            if (isset($data->access_token)) {
                return $data->access_token;
            }
        }

        return null;
    }
}
