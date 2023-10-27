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

namespace Fusio\Impl\Service\App;

use DateInterval;
use DateTime;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\App\GeneratedTokenEvent;
use Fusio\Impl\Event\App\RemovedTokenEvent;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Table;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Token
{
    private Table\App $appTable;
    private Table\User $userTable;
    private Table\App\Token $appTokenTable;
    private ConfigInterface $config;
    private JsonWebToken $jsonWebToken;
    private EventDispatcherInterface  $eventDispatcher;

    public function __construct(Table\App $appTable, Table\User $userTable, Table\App\Token $appTokenTable, ConfigInterface $config, JsonWebToken $jsonWebToken, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable        = $appTable;
        $this->userTable       = $userTable;
        $this->appTokenTable   = $appTokenTable;
        $this->config          = $config;
        $this->jsonWebToken    = $jsonWebToken;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function generateAccessToken(int $appId, int $userId, array $scopes, string $ip, DateInterval $expire, ?string $state = null): AccessToken
    {
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No scopes provided');
        }

        $app  = $this->getApp($appId);
        $user = $this->getUser($userId);

        $now     = new \DateTime();
        $expires = new \DateTime();
        $expires->add($expire);

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateToken();

        $row = new Table\Generated\AppTokenRow();
        $row->setAppId($app->getId());
        $row->setUserId($user->getId());
        $row->setStatus(Table\App\Token::STATUS_ACTIVE);
        $row->setToken($accessToken);
        $row->setRefresh($refreshToken);
        $row->setScope(implode(',', $scopes));
        $row->setIp($ip);
        $row->setExpire(LocalDateTime::from($expires));
        $row->setDate(LocalDateTime::now());
        $this->appTokenTable->create($row);

        $tokenId = $this->appTokenTable->getLastInsertId();

        // dispatch event
        $this->eventDispatcher->dispatch(new GeneratedTokenEvent(
            $appId,
            $tokenId,
            $accessToken,
            $scopes,
            $expires,
            $now,
            new UserContext($appId, $userId, $ip)
        ));

        return new AccessToken(
            $accessToken,
            'bearer',
            $expires->getTimestamp() - $now->getTimestamp(),
            $refreshToken,
            implode(',', $scopes),
            $state
        );
    }

    public function refreshAccessToken(int $appId, string $refreshToken, string $ip, DateInterval $expireApp, DateInterval $expireRefresh): AccessToken
    {
        $token = $this->appTokenTable->getTokenByRefreshToken($appId, $refreshToken);
        if (empty($token)) {
            throw new StatusCode\BadRequestException('Invalid refresh token');
        }

        // check expire date
        $now = new \DateTime();
        $date = $token->getDate()->toDateTime();
        $expires = $date->add($expireRefresh);

        if ($expires < $now) {
            throw new StatusCode\BadRequestException('Refresh token is expired');
        }

        // check whether the refresh was requested from the same app
        if ($token->getAppId() != $appId) {
            throw new StatusCode\BadRequestException('Token was requested from another app');
        }

        $app  = $this->getApp($token->getAppId());
        $user = $this->getUser($token->getUserId());

        $scopes  = explode(',', $token->getScope());
        $expires = new \DateTime();
        $expires->add($expireApp);

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateToken();

        $token->setStatus(Table\App\Token::STATUS_ACTIVE);
        $token->setToken($accessToken);
        $token->setRefresh($refreshToken);
        $token->setIp($ip);
        $token->setExpire(LocalDateTime::from($expires));
        $token->setDate(LocalDateTime::from($now));
        $this->appTokenTable->update($token);

        // dispatch event
        $this->eventDispatcher->dispatch(new GeneratedTokenEvent(
            $app->getId(),
            $token->getId(),
            $accessToken,
            $scopes,
            $expires,
            $now,
            new UserContext($token->getUserId(), $app->getId(), $ip)
        ));

        return new AccessToken(
            $accessToken,
            'bearer',
            $expires->getTimestamp() - $now->getTimestamp(),
            $refreshToken,
            implode(',', $scopes)
        );
    }

    public function removeToken(int $appId, int $tokenId, UserContext $context): void
    {
        $app = $this->getApp($appId);

        $this->appTokenTable->removeTokenFromApp($app->getId(), $tokenId);

        $this->eventDispatcher->dispatch(new RemovedTokenEvent($appId, $tokenId, $context));
    }

    private function generateJWT(Table\Generated\UserRow $user, DateTime $now, DateTime $expires): string
    {
        $baseUrl = $this->config->get('psx_url');

        $payload = [
            'iss'  => $baseUrl,
            'sub'  => Uuid::nameBased($baseUrl . '-' . $user->getId()),
            'iat'  => $now->getTimestamp(),
            'exp'  => $expires->getTimestamp(),
            'name' => $user->getName()
        ];

        return $this->jsonWebToken->encode($payload);
    }

    private function getApp(int $appId): Table\Generated\AppRow
    {
        $app = $this->appTable->find($appId);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Invalid app');
        }

        if ($app->getStatus() != Table\App::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid app status');
        }

        return $app;
    }

    private function getUser(int $userId): Table\Generated\UserRow
    {
        $user = $this->userTable->find($userId);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Invalid user');
        }

        if ($user->getStatus() != Table\User::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid user status');
        }

        return $user;
    }
}
