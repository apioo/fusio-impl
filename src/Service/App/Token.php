<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Event\AppEvents;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\Oauth2\AccessToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Token
{
    /**
     * @var \Fusio\Impl\Table\App
     */
    private $appTable;

    /**
     * @var \Fusio\Impl\Table\User 
     */
    private $userTable;

    /**
     * @var \Fusio\Impl\Table\App\Token 
     */
    private $appTokenTable;

    /**
     * @var \PSX\Framework\Config\Config 
     */
    private $config;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface 
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\App $appTable
     * @param \Fusio\Impl\Table\User $userTable
     * @param \Fusio\Impl\Table\App\Token $appTokenTable
     * @param \PSX\Framework\Config\Config $config
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\App $appTable, Table\User $userTable, Table\App\Token $appTokenTable, Config $config, EventDispatcherInterface $eventDispatcher)
    {
        $this->appTable        = $appTable;
        $this->userTable       = $userTable;
        $this->appTokenTable   = $appTokenTable;
        $this->config          = $config;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param integer $appId
     * @param integer $userId
     * @param array $scopes
     * @param string $ip
     * @param \DateInterval $expire
     * @return \PSX\Oauth2\AccessToken
     */
    public function generateAccessToken($appId, $userId, array $scopes, $ip, DateInterval $expire)
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

        $this->appTokenTable->create([
            'app_id'  => $app['id'],
            'user_id' => $user['id'],
            'status'  => Table\App\Token::STATUS_ACTIVE,
            'token'   => $accessToken,
            'refresh' => $refreshToken,
            'scope'   => implode(',', $scopes),
            'ip'      => $ip,
            'expire'  => $expires,
            'date'    => $now,
        ]);

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

        $token = new AccessToken();
        $token->setAccessToken($accessToken);
        $token->setTokenType('bearer');
        $token->setExpiresIn($expires->getTimestamp());
        $token->setRefreshToken($refreshToken);
        $token->setScope(implode(',', $scopes));

        return $token;
    }

    /**
     * @param integer $appId
     * @param string $refreshToken
     * @param string $ip
     * @param \DateInterval $expireApp
     * @param \DateInterval $expireRefresh
     * @return \PSX\Oauth2\AccessToken
     */
    public function refreshAccessToken($appId, $refreshToken, $ip, DateInterval $expireApp, DateInterval $expireRefresh)
    {
        $token = $this->appTokenTable->getTokenByRefreshToken($appId, $refreshToken);
        $now   = new \DateTime();

        if (empty($token)) {
            throw new StatusCode\BadRequestException('Invalid refresh token');
        }

        // check expire date
        $date = $token['date'];
        if ($date instanceof \DateTime) {
            $expires = clone $date;
            $expires->add($expireRefresh);

            if ($expires < $now) {
                throw new StatusCode\BadRequestException('Refresh token is expired');
            }
        }

        // check whether the refresh was requested from the same app
        if ($token['app_id'] != $appId) {
            throw new StatusCode\BadRequestException('Token was requested from another app');
        }

        $app  = $this->getApp($token['app_id']);
        $user = $this->getUser($token['user_id']);

        $scopes  = explode(',', $token['scope']);
        $expires = new \DateTime();
        $expires->add($expireApp);

        // generate access token
        $accessToken  = $this->generateJWT($user, $now, $expires);
        $refreshToken = TokenGenerator::generateToken();

        $this->appTokenTable->update([
            'id'      => $token['id'],
            'status'  => Table\App\Token::STATUS_ACTIVE,
            'token'   => $accessToken,
            'refresh' => $refreshToken,
            'ip'      => $ip,
            'expire'  => $expires,
            'date'    => $now,
        ]);

        // dispatch event
        $this->eventDispatcher->dispatch(new GeneratedTokenEvent(
            $app['id'],
            $token['id'],
            $accessToken,
            $scopes,
            $expires,
            $now,
            new UserContext($app['id'], $token['user_id'], $ip)
        ));

        $token = new AccessToken();
        $token->setAccessToken($accessToken);
        $token->setTokenType('bearer');
        $token->setExpiresIn($expires->getTimestamp());
        $token->setRefreshToken($refreshToken);
        $token->setScope(implode(',', $scopes));

        return $token;
    }

    /**
     * @param integer $appId
     * @param integer $tokenId
     * @param \Fusio\Impl\Authorization\UserContext $context
     */
    public function removeToken($appId, $tokenId, UserContext $context)
    {
        $app = $this->getApp($appId);

        $this->appTokenTable->removeTokenFromApp($app['id'], $tokenId);

        $this->eventDispatcher->dispatch(new RemovedTokenEvent($appId, $tokenId, $context));
    }

    /**
     * @param \PSX\Record\Record $user
     * @param \DateTime $now
     * @param \DateTime $expires
     * @return string
     */
    private function generateJWT($user, DateTime $now, DateTime $expires)
    {
        $baseUrl = $this->config->get('psx_url');

        $payload = [
            'iss'  => $baseUrl,
            'sub'  => Uuid::nameBased($baseUrl . '-' . $user['id']),
            'iat'  => $now->getTimestamp(),
            'exp'  => $expires->getTimestamp(),
            'name' => $user['name']
        ];

        return JWT::encode($payload, $this->config->get('fusio_project_key'));
    }

    /**
     * @param integer $appId
     * @return \PSX\Record\Record
     */
    private function getApp($appId)
    {
        $app = $this->appTable->get($appId);

        if (empty($app)) {
            throw new StatusCode\BadRequestException('Invalid app');
        }

        if ($app['status'] != Table\App::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid app status');
        }

        return $app;
    }

    /**
     * @param integer $userId
     * @return \PSX\Record\Record
     */
    private function getUser($userId)
    {
        $user = $this->userTable->get($userId);

        if (empty($user)) {
            throw new StatusCode\BadRequestException('Invalid user');
        }

        if ($user['status'] != Table\User::STATUS_ADMINISTRATOR && $user['status'] != Table\User::STATUS_CONSUMER) {
            throw new StatusCode\BadRequestException('Invalid user status');
        }

        return $user;
    }
}
