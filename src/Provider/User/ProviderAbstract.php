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

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Base;
use Fusio\Impl\Service\Config;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\Oauth2\Error;
use PSX\Uri\Url;

/**
 * ProviderAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
abstract class ProviderAbstract implements ProviderInterface
{
    protected const TYPE_POST = 0x1;
    protected const TYPE_GET = 0x2;

    protected ClientInterface $httpClient;
    protected string $secret;

    public function __construct(ClientInterface $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->secret     = $config->getValue($this->getProviderConfigKey($this->getId()));
    }

    protected function obtainUserInfo(string $rawUrl, string $accessToken, ?array $parameters = null): ?\stdClass
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'User-Agent'    => Base::getUserAgent()
        ];

        $url = new Url($rawUrl);
        if (!empty($parameters)) {
            $url = $url->withParameters($parameters);
        }

        $response = $this->httpClient->request(new GetRequest($url, $headers));
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = Parser::decode((string) $response->getBody());
        if (!$data instanceof \stdClass) {
            return null;
        }

        return $data;
    }

    protected function obtainAccessToken(string $rawUrl, array $params, int $type = self::TYPE_POST): ?string
    {
        $headers = [
            'Accept'     => 'application/json',
            'User-Agent' => Base::getUserAgent()
        ];

        if ($type === self::TYPE_POST) {
            $request = new PostRequest(new Url($rawUrl), $headers, $params);
        } elseif ($type === self::TYPE_GET) {
            $url = new Url($rawUrl);
            $url = $url->withParameters($params);
            $request = new GetRequest($url, $headers);
        } else {
            throw new \RuntimeException('Provided an invalid type');
        }

        $response = $this->httpClient->request($request);
        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = Parser::decode((string) $response->getBody(), true);
        if (isset($data['access_token']) && is_string($data['access_token'])) {
            return $data['access_token'];
        } elseif (isset($data['error']) && is_string($data['error'])) {
            $error = Error::fromArray($data);
            throw new StatusCode\BadRequestException($error->getError() . ': ' . $error->getErrorDescription() . ' (' . $error->getErrorUri() . ')');
        } else {
            return null;
        }
    }

    protected function getProviderConfigKey(int $provider): string
    {
        return match ($provider) {
            self::PROVIDER_GITHUB => 'provider_github_secret',
            self::PROVIDER_GOOGLE => 'provider_google_secret',
            self::PROVIDER_FACEBOOK => 'provider_facebook_secret',
            default => '',
        };
    }
}
