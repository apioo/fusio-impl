<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Connection;

use Fusio\Engine\ConfigurableInterface;
use Fusio\Engine\Connection\OAuth2Interface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Factory;
use Fusio\Engine\Model;
use Fusio\Engine\Parameters;
use Fusio\Engine\Repository;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Base;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Http\ResponseInterface;
use PSX\OAuth2\AccessToken;
use PSX\Uri\Url;

/**
 * This service help to manage and obtain access tokens for configured connections
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Token
{
    public function __construct(
        private Factory\ConnectionInterface $factory,
        private Repository\ConnectionInterface $repository,
        private ClientInterface $httpClient,
        private Table\Connection $connectionTable,
        private Service\Security\JsonWebToken $jsonWebToken,
        private Service\System\FrameworkConfig $frameworkConfig
    ) {
    }

    /**
     * Returns whether the provided connection id supports an OAuth2 flow
     */
    public function isValid(string $connectionId): bool
    {
        try {
            $this->getImplementation($this->getConnection($connectionId));
            return true;
        } catch (StatusCode\BadRequestException $e) {
            return false;
        }
    }

    /**
     * Returns the redirect uri for a provided connection. This works only in case the connection implements the
     * OAuth2Interface
     *
     * @throws ConfigurationException
     */
    public function buildRedirectUri(string $connectionId): string
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = new Parameters($connection->getConfig());

        $redirectUri = $this->newRedirectUri($connectionId);
        $state = $this->newState();

        $url = Url::parse($implementation->getAuthorizationUrl($config));
        $url = $url->withParameters($implementation->getRedirectUriParameters($redirectUri, $state, $config));

        return $url->toString();
    }

    /**
     * Obtains an access token by the provided code and persists the access token at the connection config
     *
     * @throws ConfigurationException
     */
    public function fetchByCode(string $connectionId, string $code, string $state, UserContext $context): void
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = new Parameters($connection->getConfig());

        $this->assertState($state);

        $redirectUri = $this->newRedirectUri($connectionId);

        $tokenUrl = $implementation->getTokenUrl($config);
        $params = $implementation->getAuthorizationCodeParameters($code, $redirectUri, $config);

        $accessToken = $this->request($tokenUrl, $params);

        $this->persistAccessTokenToConfig($connection, $accessToken, $context);
    }

    /**
     * Obtains an access token by a refresh token and persists the access token at the connection config
     *
     * @throws ConfigurationException
     */
    public function fetchByRefreshToken(string $connectionId, UserContext $context): void
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = new Parameters($connection->getConfig());

        $tokenUrl = $implementation->getTokenUrl($config);
        $params = $implementation->getRefreshTokenParameters($config);

        $accessToken = $this->request($tokenUrl, $params);

        $this->persistAccessTokenToConfig($connection, $accessToken, $context);
    }

    public function refreshAll(UserContext $context): void
    {
        $connections = $this->repository->getAll();
        foreach ($connections as $connection) {
            $parameters = new Parameters($connection->getConfig());
            $implementation = $this->factory->factory($connection->getClass());
            if (!$implementation instanceof OAuth2Interface || !$implementation instanceof ConfigurableInterface) {
                continue;
            }

            $refreshToken = $parameters->get(OAuth2Interface::CONFIG_REFRESH_TOKEN);
            if (empty($refreshToken)) {
                // we can only extend in case we have a refresh token
                continue;
            }

            try {
                $this->fetchByRefreshToken($implementation->getName(), $context);
            } catch (ConfigurationException|StatusCode\BadRequestException $e) {
                // remove refresh token in case it fails
                $parameters->set(OAuth2Interface::CONFIG_REFRESH_TOKEN, '');
                $this->persistConfig($connection, $parameters->toArray(), $context);
            }
        }
    }

    private function persistAccessTokenToConfig(Model\ConnectionInterface $connection, AccessToken $token, UserContext $context): void
    {
        $config = $connection->getConfig();
        $config[OAuth2Interface::CONFIG_ACCESS_TOKEN] = $token->getAccessToken();

        $expiresIn = $token->getExpiresIn();
        if ($expiresIn !== null) {
            $config[OAuth2Interface::CONFIG_EXPIRES_IN] = $expiresIn;
        }

        $refreshToken = $token->getRefreshToken();
        if ($refreshToken !== null) {
            $config[OAuth2Interface::CONFIG_REFRESH_TOKEN] = $refreshToken;
        }

        $this->persistConfig($connection, $config, $context);
    }

    private function persistConfig(Model\ConnectionInterface $connection, array $config, UserContext $context): void
    {
        $row = $this->connectionTable->findOneByTenantAndId($context->getTenantId(), null, $connection->getId());
        $row->setConfig(Encrypter::encrypt($config, $this->frameworkConfig->getProjectKey()));
        $this->connectionTable->update($row);
    }

    /**
     * @throws StatusCode\BadRequestException
     */
    private function getConnection(string $connectionId): Model\ConnectionInterface
    {
        $connection = $this->repository->get($connectionId);
        if (!$connection instanceof Model\ConnectionInterface) {
            throw new StatusCode\BadRequestException('Could not found connection ' . $connectionId);
        }

        return $connection;
    }

    /**
     * @throws StatusCode\BadRequestException
     */
    private function getImplementation(Model\ConnectionInterface $connection): OAuth2Interface
    {
        $implementation = $this->factory->factory($connection->getClass());
        if (!$implementation instanceof OAuth2Interface) {
            throw new StatusCode\BadRequestException('Provided connection does not support OAuth2');
        }

        return $implementation;
    }

    private function request(string $tokenUrl, array $body): AccessToken
    {
        $headers = [
            'User-Agent' => Base::getUserAgent(),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $request  = new PostRequest($tokenUrl, $headers, $body);
        $response = $this->httpClient->request($request);

        return $this->parseAccessToken($response);
    }

    /**
     * @throws StatusCode\BadRequestException
     */
    private function parseAccessToken(ResponseInterface $response): AccessToken
    {
        if ($response->getStatusCode() !== 200) {
            throw new StatusCode\BadRequestException('Could not obtain access token');
        }

        $data = \json_decode((string) $response->getBody());
        if (!$data instanceof \stdClass) {
            throw new StatusCode\BadRequestException('Could not obtain access token');
        }

        $accessToken = $data->access_token ?? null;
        if (empty($accessToken)) {
            throw new StatusCode\BadRequestException('Provided an invalid access token');
        }

        $expiresIn = $data->expires_in ?? null;
        if (!empty($expiresIn)) {
            // expires in is only recommended but if available we validate the value
            if (!is_int($expiresIn)) {
                throw new StatusCode\BadRequestException('Provided an invalid expires in value');
            }
        }

        $refreshToken = $data->refresh_token ?? null;
        $scope = $data->scope ?? null;

        return new AccessToken(
            $accessToken,
            'bearer',
            $expiresIn,
            $refreshToken,
            $scope
        );
    }

    /**
     * @throws StatusCode\BadRequestException
     */
    private function assertState(string $state): void
    {
        try {
            $this->jsonWebToken->decode($state);
        } catch (\UnexpectedValueException $e) {
            throw new StatusCode\BadRequestException('The provided state is not valid', $e);
        }
    }

    private function newState(): string
    {
        $payload = [
            'exp' => time() + (60 * 10)
        ];

        return $this->jsonWebToken->encode($payload);
    }

    private function newRedirectUri(string $connectionId): string
    {
        return $this->frameworkConfig->getDispatchUrl('system', 'connection', $connectionId, 'callback');
    }
}
