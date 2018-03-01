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

    public function __construct(Connection $connection, Context $context, $projectKey, AppInterface $appRepository, UserInterface $userRepository)
    {
        $this->connection     = $connection;
        $this->context        = $context;
        $this->projectKey     = $projectKey;
        $this->appRepository  = $appRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        if ($request->getMethod() === 'OPTIONS') {
            $needsAuth = false;
        } else {
            $method = $this->context->getMethod();
            if (isset($method['public'])) {
                $needsAuth = !$method['public'];
            } else {
                $needsAuth = false;
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

                if (!empty($token)) {
                    $app  = $this->appRepository->get($token['appId']);
                    $user = $this->userRepository->get($token['userId']);

                    $this->context->setApp($app);
                    $this->context->setUser($user);

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

            $this->context->setApp($app);
            $this->context->setUser($user);

            $filterChain->handle($request, $response);
        }
    }

    protected function getToken($token, $requestMethod)
    {
        // if a user sends a JWT which was obtained through the consumer login
        // we extract the access token
        if (strpos($token, '.') !== false) {
            $jwt   = JWT::decode($token, $this->projectKey, ['HS256']);
            $token = isset($jwt->sub) ? $jwt->sub : null;
        }

        $now = new \DateTime();
        $sql = 'SELECT appToken.appId,
                       appToken.userId,
                       appToken.token,
                       appToken.scope,
                       appToken.expire,
                       appToken.date
                  FROM fusio_app_token appToken
                 WHERE appToken.token = :token
                   AND appToken.status = :status
                   AND (appToken.expire IS NULL OR appToken.expire > :now)';

        $accessToken = $this->connection->fetchAssoc($sql, array(
            'token'  => $token,
            'status' => AppToken::STATUS_ACTIVE,
            'now'    => $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()),
        ));

        if (!empty($accessToken)) {
            // these are the scopes which are assigned to the token
            $entitledScopes = explode(',', $accessToken['scope']);

            // get all scopes which are assigned to this route
            $sql = '    SELECT scope.name,
                               scopeRoutes.allow,
                               scopeRoutes.methods
                          FROM fusio_scope_routes scopeRoutes
                    INNER JOIN fusio_scope scope
                            ON scope.id = scopeRoutes.scopeId
                         WHERE scopeRoutes.routeId = :route';

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

            if ($isAllowed) {
                return $accessToken;
            } else {
                throw new InvalidScopeException('Access to this resource is not in the scope of the provided token');
            }
        }

        return null;
    }
}
