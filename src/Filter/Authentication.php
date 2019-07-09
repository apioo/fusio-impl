<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Filter;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use Fusio\Engine\Model;
use Fusio\Engine\Repository\AppInterface;
use Fusio\Engine\Repository\UserInterface;
use Fusio\Impl\Loader\Context;
use Fusio\Impl\Table\App\Token as AppToken;
use PSX\Http\Exception\UnauthorizedException;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Oauth2\Authorization\Exception\InvalidScopeException;

/**
 * Authentication
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Authentication implements FilterInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    /**
     * @var string
     */
    protected $projectKey;

    /**
     * @var \Fusio\Engine\Repository\AppInterface
     */
    protected $appRepository;

    /**
     * @var \Fusio\Engine\Repository\UserInterface
     */
    protected $userRepository;

    /**
     * @var boolean
     */
    protected $assertScope;

    public function __construct(Connection $connection, Context $context, $projectKey, AppInterface $appRepository, UserInterface $userRepository, bool $assertScope = true)
    {
        $this->connection     = $connection;
        $this->context        = $context;
        $this->projectKey     = $projectKey;
        $this->appRepository  = $appRepository;
        $this->userRepository = $userRepository;
        $this->assertScope    = $assertScope;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $needsAuth = false;
        } else {
            $method = $this->context->getMethod();
            if (is_array($method)) {
                $needsAuth = !$method['public'];
            } else {
                $needsAuth = true;
            }
        }

        $requestMethod = $request->getMethod() == 'HEAD' ? 'GET' : $request->getMethod();
        $authorization = $request->getHeader('Authorization');

        // authorization is required if the method is not public. In case we get
        // a header from the client we also check the token so that the client
        // gets maybe another rate limit
        if ($needsAuth || !empty($authorization)) {
            $parts       = explode(' ', $authorization, 2);
            $type        = isset($parts[0]) ? $parts[0] : null;
            $accessToken = isset($parts[1]) ? $parts[1] : null;

            $params = array(
                'realm' => 'Fusio',
            );

            if ($type == 'Bearer' && !empty($accessToken)) {
                $token = $this->getToken($accessToken, $requestMethod);

                if ($token instanceof Model\Token) {
                    $app  = $this->appRepository->get($token->getAppId());
                    $user = $this->userRepository->get($token->getUserId());

                    $this->context->setApp($app);
                    $this->context->setUser($user);
                    $this->context->setToken($token);

                    $filterChain->handle($request, $response);
                } else {
                    throw new UnauthorizedException('Invalid access token', 'Bearer', $params);
                }
            } else {
                throw new UnauthorizedException('Missing authorization header', 'Bearer', $params);
            }
        } else {
            $app = new Model\App();
            $app->setAnonymous(true);
            $app->setScopes([]);

            $user = new Model\User();
            $user->setAnonymous(true);

            $token = new Model\Token();
            $token->setScopes([]);

            $this->context->setApp($app);
            $this->context->setUser($user);
            $this->context->setToken($token);

            $filterChain->handle($request, $response);
        }
    }

    /**
     * @param string $token
     * @param string $requestMethod
     * @return \Fusio\Engine\Model\Token|null
     * @throws \Exception
     */
    protected function getToken($token, $requestMethod)
    {
        // @TODO in the latest version we only issue JWTs so in the next major
        // release we can always decode the token
        if (strpos($token, '.') !== false) {
            JWT::decode($token, $this->projectKey, ['HS256']);
        }

        $now = new \DateTime();
        $sql = 'SELECT app_token.id,
                       app_token.app_id,
                       app_token.user_id,
                       app_token.token,
                       app_token.scope,
                       app_token.expire,
                       app_token.date
                  FROM fusio_app_token app_token
                 WHERE app_token.token = :token
                   AND app_token.status = :status
                   AND (app_token.expire IS NULL OR app_token.expire > :now)';

        $accessToken = $this->connection->fetchAssoc($sql, array(
            'token'  => $token,
            'status' => AppToken::STATUS_ACTIVE,
            'now'    => $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()),
        ));

        if (!empty($accessToken)) {
            if ($this->assertScope !== false) {
                // these are the scopes which are assigned to the token
                $entitledScopes = explode(',', $accessToken['scope']);

                // get all scopes which are assigned to this route
                $sql = '    SELECT scope.name,
                                   scope_routes.allow,
                                   scope_routes.methods
                              FROM fusio_scope_routes scope_routes
                        INNER JOIN fusio_scope scope
                                ON scope.id = scope_routes.scope_id
                             WHERE scope_routes.route_id = :route';

                $availableScopes = $this->connection->fetchAll($sql, array('route' => $this->context->getRouteId()));

                // now we check whether the assigned scopes are allowed to
                // access this route. We must have at least one scope which
                // explicit allows the request
                $isAllowed = false;

                foreach ($entitledScopes as $entitledScope) {
                    foreach ($availableScopes as $scope) {
                        if ($scope['name'] == $entitledScope && $scope['allow'] == 1 && in_array($requestMethod, explode('|', $scope['methods']))) {
                            $isAllowed = true;
                            break 2;
                        }
                    }
                }
            } else {
                $entitledScopes = [];
                $isAllowed = true;
            }

            if ($isAllowed) {
                $return = new Model\Token();
                $return->setId($accessToken['id']);
                $return->setAppId($accessToken['app_id']);
                $return->setUserId($accessToken['user_id']);
                $return->setScopes($entitledScopes);
                $return->setExpire($accessToken['expire']);
                $return->setDate($accessToken['date']);

                return $return;
            } else {
                throw new InvalidScopeException('Access to this resource is not in the scope of the provided token');
            }
        }

        return null;
    }
}
