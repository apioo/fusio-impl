<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Service;

use DateInterval;
use DateTime;
use Firebase\JWT\JWT;
use Fusio\Impl\Service\Consumer\Model\User as ModelUser;
use Fusio\Impl\Service\Consumer\ProviderInterface;
use Fusio\Impl\Table\App as TableApp;
use Fusio\Impl\Table\Scope as TableScope;
use Fusio\Impl\Table\User as TableUser;
use PSX\Http\Exception as StatusCode;
use PSX\Http;
use PSX\Sql\Condition;
use RuntimeException;

/**
 * Consumer
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Consumer
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $user;

    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $app;

    /**
     * @var \PSX\Http\Client
     */
    protected $httpClient;

    /**
     * @var array
     */
    protected $providers;

    /**
     * @var string
     */
    protected $tokenSecret;

    /**
     * @var string
     */
    protected $expires;

    /**
     * @var string
     */
    protected $scopes;

    public function __construct(User $user, App $app, Http\Client $httpClient, array $providers, $tokenSecret, $expires, $scopes)
    {
        $this->user        = $user;
        $this->app         = $app;
        $this->httpClient  = $httpClient;
        $this->providers   = $providers;
        $this->tokenSecret = $tokenSecret;
        $this->expires     = $expires;
        $this->scopes      = $scopes;
    }

    public function login($name, $password)
    {
        $userId = $this->user->authenticateUser($name, $password, [TableUser::STATUS_ADMINISTRATOR, TableUser::STATUS_CONSUMER]);
        if ($userId > 0) {
            return $this->createToken($userId, $this->user->getAvailableScopes($userId));
        }

        return null;
    }
    
    public function register($name, $email, $password)
    {
        $scopes = $this->getDefaultScopes();
        $userId = $this->user->create(
            TableUser::STATUS_CONSUMER,
            $name,
            $email,
            $password,
            $scopes
        );

        return $this->createToken($userId, $scopes);
    }

    public function provider($providerName, $code, $clientId, $redirectUri)
    {
        $providerName = strtolower($providerName);
        $provider     = $this->getProvider($providerName);

        if ($provider instanceof ProviderInterface) {
            $config = isset($this->providers[$providerName]) ? $this->providers[$providerName] : null;

            if (is_array($config)) {
                $user = $provider->requestUser($code, $clientId, $redirectUri, $config);

                if ($user instanceof ModelUser) {
                    $scopes = $this->getDefaultScopes();
                    $userId = $this->user->createRemote(
                        $provider->getId(),
                        $user->getId(),
                        $user->getName(),
                        $user->getEmail(),
                        $scopes
                    );

                    return $this->createToken($userId, $scopes);
                } else {
                    throw new StatusCode\BadRequestException('Could not request user informations');
                }
            } else {
                throw new StatusCode\BadRequestException('Not supported provider');
            }
        } else {
            throw new StatusCode\BadRequestException('Not supported provider');
        }
    }

    protected function createToken($userId, array $scopes)
    {
        // @TODO we need an id of an app this must be probably the fusio 
        // consumer app
        $appId = 1;

        $token = $this->app->generateAccessToken(
            $appId,
            $userId,
            $scopes,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new DateInterval($this->expires)
        );

        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => $token->getExpiresIn(),
            'jti' => $token->getAccessToken(),
        ];

        return JWT::encode($payload, $this->tokenSecret);
    }

    protected function getProvider($provider)
    {
        switch ($provider) {
            case 'facebook':
                return new Consumer\Provider\Facebook($this->httpClient);

            case 'github':
                return new Consumer\Provider\Github($this->httpClient);

            case 'google':
                return new Consumer\Provider\Google($this->httpClient);
        }

        return null;
    }

    protected function getDefaultScopes()
    {
        return array_filter(array_map('trim', explode(',', $this->scopes)), function($scope){
            // we filter out the backend scope since this would be a major
            // security issue
            return !empty($scope) && $scope != 'backend';
        });
    }
}
