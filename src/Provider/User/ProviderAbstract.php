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

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Base;
use Fusio\Impl\Service\Config;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\GetRequest;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;
use PSX\OAuth2\Error;
use PSX\Uri\Url;

/**
 * ProviderAbstract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
abstract class ProviderAbstract implements ProviderInterface
{
    protected const TYPE_POST = 0x1;
    protected const TYPE_GET = 0x2;

    private ClientInterface $httpClient;
    private Config $config;

    public function __construct(ClientInterface $httpClient, Config $config)
    {
        $this->httpClient = $httpClient;
        $this->config = $config;
    }

    public function setHttpClient(ClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    protected function obtainUserInfo(string $rawUrl, string $accessToken, ?array $parameters = null): ?\stdClass
    {
        $headers = [
            'Authorization' => 'Bearer ' . $accessToken,
            'User-Agent'    => Base::getUserAgent()
        ];

        $url = Url::parse($rawUrl);
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
            $request = new PostRequest(Url::parse($rawUrl), $headers, $params);
        } elseif ($type === self::TYPE_GET) {
            $url = Url::parse($rawUrl);
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
        if (!is_array($data)) {
            return null;
        }

        if (isset($data['access_token']) && is_string($data['access_token'])) {
            return $data['access_token'];
        } elseif (isset($data['error']) && is_string($data['error'])) {
            $error = Error::fromArray($data);
            throw new StatusCode\BadRequestException($error->getError() . ': ' . $error->getErrorDescription() . ' (' . $error->getErrorUri() . ')');
        } else {
            return null;
        }
    }

    protected function getSecret(): string
    {
        return $this->config->getValue($this->getProviderConfigKey($this->getId()));
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
