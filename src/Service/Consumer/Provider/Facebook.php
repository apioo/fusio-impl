<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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
use PSX\Json\Parser;
use PSX\Uri\Url;
use RuntimeException;

/**
 * Facebook
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Facebook extends ProviderAbstract
{
    public function getId()
    {
        return self::PROVIDER_FACEBOOK;
    }

    public function requestUser($code, $clientId, $redirectUri, array $config)
    {
        $clientSecret = isset($config['secret']) ? $config['secret'] : null;
        $accessToken  = $this->getAccessToken($code, $clientId, $clientSecret, $redirectUri);

        if (!empty($accessToken)) {
            $url      = new Url('https://graph.facebook.com/v2.5/me');
            $headers  = ['Authorization' => 'Bearer ' . $accessToken, 'User-Agent' => $this->ua];

            $url = $url->withParameters([
                'access_token' => $accessToken,
                'fields'       => 'id,email,first_name,last_name,link,name',
            ]);

            $response = $this->httpClient->request(new GetRequest($url, $headers));

            if ($response->getStatusCode() == 200) {
                $data  = Parser::decode($response->getBody());
                $id    = isset($data->id) ? $data->id : null;
                $name  = isset($data->name) ? $data->name : null;
                $email = isset($data->email) ? $data->email : null;

                if (!empty($id) && !empty($name) && !empty($email)) {
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

        $url = new Url('https://graph.facebook.com/v2.5/oauth/access_token');
        $url = $url->withParameters([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'client_secret' => $clientSecret,
            'code' => $code,
        ]);

        $response = $this->httpClient->request(new GetRequest($url));

        if ($response->getStatusCode() == 200) {
            $data = Parser::decode($response->getBody());
            if (isset($data->access_token)) {
                return $data->access_token;
            }
        }

        return null;
    }
}
