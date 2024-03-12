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

namespace Fusio\Impl\Service;

use DateInterval;
use DateTime;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Token\GeneratedEvent;
use Fusio\Impl\Event\Token\RemovedEvent;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
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
    private Table\Token $tokenTable;
    private FrameworkConfig $frameworkConfig;
    private JsonWebToken $jsonWebToken;
    private EventDispatcherInterface  $eventDispatcher;

    public function __construct(Table\App $appTable, Table\User $userTable, Table\Token $tokenTable, FrameworkConfig $frameworkConfig, JsonWebToken $jsonWebToken, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable = $appTable;
        $this->userTable = $userTable;
        $this->tokenTable = $tokenTable;
        $this->frameworkConfig = $frameworkConfig;
        $this->jsonWebToken = $jsonWebToken;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function generateAccessToken(?string $tenantId, ?int $appId, int $userId, array $scopes, string $ip, DateInterval $expire, ?string $state = null): AccessToken
    {
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No scopes provided');
        }

        $app  = $appId !== null ? $this->getApp($tenantId, $appId) : null;
        $user = $this->getUser($tenantId, $userId);

        $now     = new \DateTime();
        $expires = new \DateTime();
        $expires->add($expire);

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateToken();

        $row = new Table\Generated\TokenRow();
        $row->setTenantId($tenantId);
        $row->setAppId($app?->getId());
        $row->setUserId($user->getId());
        $row->setStatus(Table\Token::STATUS_ACTIVE);
        $row->setToken($accessToken);
        $row->setRefresh($refreshToken);
        $row->setScope(implode(',', $scopes));
        $row->setIp($ip);
        $row->setExpire(LocalDateTime::from($expires));
        $row->setDate(LocalDateTime::now());
        $this->tokenTable->create($row);

        $tokenId = $this->tokenTable->getLastInsertId();

        // dispatch event
        $this->eventDispatcher->dispatch(new GeneratedEvent(
            $tokenId,
            $accessToken,
            $scopes,
            $expires,
            $now,
            new UserContext($userId, $appId, $ip, $tenantId)
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

    public function refreshAccessToken(?string $tenantId, string $refreshToken, string $ip, DateInterval $expireApp, DateInterval $expireRefresh): AccessToken
    {
        $token = $this->tokenTable->findOneByTenantAndRefreshToken($tenantId, $refreshToken);
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

        $user = $this->getUser($tenantId, $token->getUserId());

        $scopes  = explode(',', $token->getScope());
        $expires = new \DateTime();
        $expires->add($expireApp);

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateToken();

        $token->setStatus(Table\Token::STATUS_ACTIVE);
        $token->setToken($accessToken);
        $token->setRefresh($refreshToken);
        $token->setIp($ip);
        $token->setExpire(LocalDateTime::from($expires));
        $token->setDate(LocalDateTime::from($now));
        $this->tokenTable->update($token);

        // dispatch event
        $this->eventDispatcher->dispatch(new GeneratedEvent(
            $token->getId(),
            $accessToken,
            $scopes,
            $expires,
            $now,
            new UserContext($token->getUserId(), $token->getAppId(), $ip, $tenantId)
        ));

        return new AccessToken(
            $accessToken,
            'bearer',
            $expires->getTimestamp() - $now->getTimestamp(),
            $refreshToken,
            implode(',', $scopes)
        );
    }

    public function removeToken(int $tokenId, UserContext $context): void
    {
        $this->tokenTable->removeTokenFromApp($context->getTenantId(), $tokenId);

        $this->eventDispatcher->dispatch(new RemovedEvent($tokenId, $context));
    }

    private function generateJWT(Table\Generated\UserRow $user, DateTime $now, DateTime $expires): string
    {
        $baseUrl = $this->frameworkConfig->getUrl();

        $payload = [
            'iss'  => $baseUrl,
            'sub'  => Uuid::nameBased($baseUrl . '-' . $user->getId()),
            'iat'  => $now->getTimestamp(),
            'exp'  => $expires->getTimestamp(),
            'name' => $user->getName()
        ];

        return $this->jsonWebToken->encode($payload);
    }

    private function getApp(?string $tenantId, int $appId): Table\Generated\AppRow
    {
        $app = $this->appTable->findOneByTenantAndId($tenantId, $appId);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Invalid app');
        }

        if ($app->getStatus() != Table\App::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid app status');
        }

        return $app;
    }

    private function getUser(?string $tenantId, int $userId): Table\Generated\UserRow
    {
        $user = $this->userTable->findOneByTenantAndId($tenantId, $userId);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Invalid user');
        }

        if ($user->getStatus() != Table\User::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid user status');
        }

        return $user;
    }
}
