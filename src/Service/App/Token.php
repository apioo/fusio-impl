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

namespace Fusio\Impl\Service\App;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\App\GeneratedTokenEvent;
use Fusio\Impl\Event\App\RemovedTokenEvent;
use Fusio\Impl\Table;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\Oauth2\AccessToken;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Token
{
    private Table\App $appTable;
    private Table\User $userTable;
    private Table\App\Token $appTokenTable;
    private ConfigInterface $config;
    private EventDispatcherInterface  $eventDispatcher;

    public function __construct(Table\App $appTable, Table\User $userTable, Table\App\Token $appTokenTable, ConfigInterface $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable        = $appTable;
        $this->userTable       = $userTable;
        $this->appTokenTable   = $appTokenTable;
        $this->config          = $config;
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

        $record = new Table\Generated\AppTokenRow([
            Table\Generated\AppTokenTable::COLUMN_APP_ID => $app->getId(),
            Table\Generated\AppTokenTable::COLUMN_USER_ID => $user->getId(),
            Table\Generated\AppTokenTable::COLUMN_STATUS => Table\App\Token::STATUS_ACTIVE,
            Table\Generated\AppTokenTable::COLUMN_TOKEN => $accessToken,
            Table\Generated\AppTokenTable::COLUMN_REFRESH => $refreshToken,
            Table\Generated\AppTokenTable::COLUMN_SCOPE => implode(',', $scopes),
            Table\Generated\AppTokenTable::COLUMN_IP => $ip,
            Table\Generated\AppTokenTable::COLUMN_EXPIRE => $expires,
            Table\Generated\AppTokenTable::COLUMN_DATE => $now,
        ]);

        $this->appTokenTable->create($record);

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
            $expires->getTimestamp(),
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
        $date = $token->getDate();
        if ($date instanceof \DateTime) {
            $expires = clone $date;
            $expires->add($expireRefresh);

            if ($expires < $now) {
                throw new StatusCode\BadRequestException('Refresh token is expired');
            }
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

        $record = new Table\Generated\AppTokenRow([
            Table\Generated\AppTokenTable::COLUMN_ID => $token->getId(),
            Table\Generated\AppTokenTable::COLUMN_STATUS => Table\App\Token::STATUS_ACTIVE,
            Table\Generated\AppTokenTable::COLUMN_TOKEN => $accessToken,
            Table\Generated\AppTokenTable::COLUMN_REFRESH => $refreshToken,
            Table\Generated\AppTokenTable::COLUMN_IP => $ip,
            Table\Generated\AppTokenTable::COLUMN_EXPIRE => $expires,
            Table\Generated\AppTokenTable::COLUMN_DATE => $now,
        ]);

        $this->appTokenTable->update($record);

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
            $expires->getTimestamp(),
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

        return JWT::encode($payload, $this->config->get('fusio_project_key'));
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
