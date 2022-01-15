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

namespace Fusio\Impl\Service\Connection;

use Firebase\JWT\JWT;
use Fusio\Engine\Connection\OAuth2Interface;
use Fusio\Engine\Factory;
use Fusio\Engine\Model;
use Fusio\Engine\Parameters;
use Fusio\Engine\Repository;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Base;
use Fusio\Impl\Service;
use Fusio\Model\Backend\Connection_Config;
use Fusio\Model\Backend\Connection_Update;
use PSX\Framework\Config\Config;
use PSX\Http\Client\ClientInterface;
use PSX\Http\Client\PostRequest;
use PSX\Http\Exception as StatusCode;
use PSX\Http\ResponseInterface;
use PSX\Oauth2\AccessToken;
use PSX\Uri\Url;

/**
 * This service help to manage and obtain access tokens for configured connections
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Token
{
    private Factory\ConnectionInterface $factory;
    private Repository\ConnectionInterface $repository;
    private ClientInterface $httpClient;
    private Service\Connection $connectionService;
    private Config $config;

    public function __construct(Factory\ConnectionInterface $factory, Repository\ConnectionInterface $repository, ClientInterface $httpClient, Service\Connection $connectionService, Config $config)
    {
        $this->factory = $factory;
        $this->repository = $repository;
        $this->httpClient = $httpClient;
        $this->connectionService = $connectionService;
        $this->config = $config;
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
     */
    public function buildRedirectUri(string $connectionId): string
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = $connection->getConfig();

        $redirectUri = $this->newRedirectUri($connectionId);
        $state = $this->newState();

        $url = new Url($implementation->getAuthorizationUrl());
        $url = $url->withParameters(array_merge([
            'response_type' => 'code',
            'client_id' => $config['client_id'] ?? null,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ], $url->getParameters()));

        return $url->toString();
    }

    /**
     * Obtains an access token by the provided code and persists the access token at the connection config
     */
    public function fetchByCode(string $connectionId, string $code, string $state): void
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = $connection->getConfig();

        $this->assertState($state);

        $params = [
            'client_id' => $config['client_id'] ?? null,
            'client_secret' => $config['client_secret'] ?? null,
            'code' => $code,
            'redirect_uri' => $this->buildRedirectUri($connectionId),
        ];

        $accessToken = $this->request($implementation, $params);

        $this->updateConnectionConfig($connection, $accessToken);
    }

    /**
     * Obtains an access token by a refresh token and persists the access token at the connection config
     */
    public function fetchByRefreshToken(string $connectionId): void
    {
        $connection = $this->getConnection($connectionId);
        $implementation = $this->getImplementation($connection);
        $config = $connection->getConfig();

        $params = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $config['refresh_token'] ?? null,
        ];

        $accessToken = $this->request($implementation, $params);

        $this->updateConnectionConfig($connection, $accessToken);
    }

    public function refreshAll()
    {
        $connections = $this->repository->getAll();
        foreach ($connections as $connection) {
            $parameters = new Parameters($connection->getConfig());
            $connection = $this->factory->factory($connection->getClass());
            if (!$connection instanceof OAuth2Interface) {
                continue;
            }

            $expiresIn = $parameters->get(OAuth2Interface::CONFIG_EXPIRES_IN);
            if (empty($expiresIn) || $expiresIn <= time()) {
                // we can only extend in case we have an expires in value which is in the future
                continue;
            }

            $refreshToken = $parameters->get(OAuth2Interface::CONFIG_REFRESH_TOKEN);
            if (empty($refreshToken)) {
                // we can only extend in case we have a refresh token
                continue;
            }

            $this->fetchByRefreshToken($connection->getName());
        }
    }

    private function updateConnectionConfig(Model\ConnectionInterface $connection, AccessToken $token)
    {
        $config = $connection->getConfig();
        $config[OAuth2Interface::CONFIG_ACCESS_TOKEN] = $token->getAccessToken();
        $config[OAuth2Interface::CONFIG_EXPIRES_IN] = $token->getExpiresIn();
        $config[OAuth2Interface::CONFIG_REFRESH_TOKEN] = $token->getRefreshToken();

        $update = new Connection_Update();
        $update->setConfig(new Connection_Config('config', $config));
        $this->connectionService->update($connection->getId(), $update, UserContext::newAnonymousContext());
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

    private function request(OAuth2Interface $connection, array $body): AccessToken
    {
        $headers = [
            'User-Agent' => Base::getUserAgent(),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $request  = new PostRequest($connection->getTokenUrl(), $headers, $body);
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
            if (!is_int($expiresIn) || $expiresIn < time()) {
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
    private function assertState(string $state)
    {
        try {
            JWT::decode($state, $this->config->get('fusio_project_key'), ['HS256']);
        } catch (\UnexpectedValueException $e) {
            throw new StatusCode\BadRequestException('The provided state is not valid', $e);
        }
    }

    private function newState(): string
    {
        return JWT::encode(['exp' => time() + (60 * 10)], $this->config->get('fusio_project_key'));
    }

    private function newRedirectUri(string $connectionId): string
    {
        return $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch') . 'system/connection/' . $connectionId . '/callback';
    }
}
