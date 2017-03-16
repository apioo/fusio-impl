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

namespace Fusio\Impl\Service\User\Provider;

use Fusio\Impl\Service\User\Model\User;
use Fusio\Impl\Service\User\ProviderAbstract;
use PSX\Http\GetRequest;
use PSX\Http\PostRequest;
use PSX\Json\Parser;
use PSX\Uri\Url;
use RuntimeException;

/**
 * Github
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Github extends ProviderAbstract
{
    public function getId()
    {
        return self::PROVIDER_GITHUB;
    }

    public function requestUser($code, $clientId, $redirectUri)
    {
        $accessToken = $this->getAccessToken($code, $clientId, $this->secret, $redirectUri);

        if (!empty($accessToken)) {
            $url      = new Url('https://api.github.com/user');
            $headers  = ['Authorization' => 'Bearer ' . $accessToken, 'User-Agent' => $this->ua];
            $response = $this->httpClient->request(new GetRequest($url, $headers));

            if ($response->getStatusCode() == 200) {
                $data  = Parser::decode($response->getBody());
                $id    = isset($data->id) ? $data->id: null;
                $name  = isset($data->login) ? $data->login : null;
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

        $url    = new Url('https://github.com/login/oauth/access_token');
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
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
