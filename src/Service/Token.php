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

namespace Fusio\Impl\Service;

use DateInterval;
use DateTime;
use DateTimeInterface;
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
readonly class Token
{
    public function __construct(
        private Table\App $appTable,
        private Table\User $userTable,
        private Table\Token $tokenTable,
        private Table\Category $categoryTable,
        private FrameworkConfig $frameworkConfig,
        private JsonWebToken $jsonWebToken,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    public function generate(?string $tenantId, string $categoryType, ?int $appId, int $userId, string $name, array $scopes, string $ip, DateInterval|DateTimeInterface $expire, ?string $state = null): AccessToken
    {
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No scopes provided');
        }

        $now = new DateTime();
        $categoryId = $this->categoryTable->getCategoryIdByType($tenantId, $categoryType);
        $app = $appId !== null ? $this->getApp($tenantId, $appId, $userId) : null;
        $user = $this->getUser($tenantId, $userId);
        $expires = $this->getExpires($expire, $now);

        // trim long user agents
        if (strlen($name) > 250) {
            $name = substr($name, 0, 250);
        }

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateRefreshToken();

        $row = new Table\Generated\TokenRow();
        $row->setTenantId($tenantId);
        $row->setCategoryId($categoryId);
        $row->setAppId($app?->getId());
        $row->setUserId($user->getId());
        $row->setStatus(Table\Token::STATUS_ACTIVE);
        $row->setName($name);
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
            new UserContext($categoryId, $userId, $appId, $ip, $tenantId)
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

    public function refresh(?string $tenantId, string $categoryType, string $name, string $refreshToken, string $ip, DateInterval|DateTimeInterface $expire, DateInterval $expireRefresh): AccessToken
    {
        $existing = $this->tokenTable->findOneByTenantAndRefreshToken($tenantId, $refreshToken);
        if (empty($existing)) {
            throw new StatusCode\BadRequestException('Invalid refresh token');
        }

        // check expire date
        $now = new DateTime();
        $date = $existing->getDate()->toDateTime();
        $expires = $date->add($expireRefresh);

        if ($expires < $now) {
            throw new StatusCode\BadRequestException('Refresh token is expired');
        }

        $scopes = explode(',', $existing->getScope());

        // delete existing token
        $existing->setStatus(Table\Token::STATUS_DELETED);
        $this->tokenTable->update($existing);

        return $this->generate(
            $tenantId,
            $categoryType,
            $existing->getAppId(),
            $existing->getUserId(),
            $name,
            $scopes,
            $ip,
            $expire
        );
    }

    public function remove(int $tokenId, UserContext $context): void
    {
        $this->tokenTable->removeToken($context->getTenantId(), $tokenId);

        $this->eventDispatcher->dispatch(new RemovedEvent($tokenId, $context));
    }

    private function generateJWT(Table\Generated\UserRow $user, DateTimeInterface $now, DateTimeInterface $expires): string
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

    private function getApp(?string $tenantId, int $appId, int $userId): Table\Generated\AppRow
    {
        $app = $this->appTable->findOneByTenantAndId($tenantId, $appId);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Invalid app');
        }

        if ($app->getUserId() !== $userId) {
            throw new StatusCode\BadRequestException('Provided app is not assigned to the user');
        }

        if ($app->getStatus() !== Table\App::STATUS_ACTIVE) {
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

    private function getExpires(DateInterval|DateTimeInterface $expire, DateTimeInterface $now): DateTimeInterface
    {
        if ($expire instanceof DateInterval) {
            $expires = new DateTime();
            $expires->add($expire);

            return $expires;
        } else {
            $diff = $now->diff($expire);
            if ($diff->days > 365) {
                throw new StatusCode\BadRequestException('Expire date can only be max 365 days into the future');
            }

            if ($diff->invert) {
                throw new StatusCode\BadRequestException('Expire date must be in the future');
            }

            return $expire;
        }
    }
}
