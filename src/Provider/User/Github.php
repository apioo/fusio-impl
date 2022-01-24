<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Engine\User\ProviderInterface;
use Fusio\Engine\User\UserDetails;
use Fusio\Impl\Base;
use Fusio\Impl\Service\Config;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\PostRequest;
use PSX\Json\Parser;
use PSX\Uri\Url;

/**
 * Github
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Github implements ProviderInterface
{
    private ClientInterface $httpClient;
    private string $secret;

    public function __construct(ClientInterface $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->secret     = $config->getValue('provider_github_secret');
    }

    public function getId(): int
    {
        return self::PROVIDER_GITHUB;
    }

    public function requestUser(string $code, string $clientId, string $redirectUri): ?UserDetails
    {
        $accessToken = $this->getAccessToken($code, $clientId, $this->secret, $redirectUri);
        if (empty($accessToken)) {
            return null;
        }

        $url = new Url('https://api.github.com/user');

        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'User-Agent'    => Base::getUserAgent()
        ];

        $response = $this->httpClient->request(new GetRequest($url, $headers));
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data  = Parser::decode((string) $response->getBody());
        $id    = $data->id ?? null;
        $name  = $data->login ?? null;
        $email = $data->email ?? null;

        if (!empty($id) && !empty($name)) {
            return new UserDetails($id, $name, $email);
        } else {
            return null;
        }
    }

    protected function getAccessToken($code, $clientId, $clientSecret, $redirectUri)
    {
        $url = new Url('https://github.com/login/oauth/access_token');
        $params = [
            'code'          => $code,
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri'  => $redirectUri,
        ];

        $headers = [
            'Accept'     => 'application/json',
            'User-Agent' => Base::getUserAgent()
        ];

        $response = $this->httpClient->request(new PostRequest($url, $headers, $params));
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = Parser::decode((string) $response->getBody());
        if (isset($data->access_token)) {
            return $data->access_token;
        } else {
            return null;
        }
    }
}
