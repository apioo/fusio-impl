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

namespace Fusio\Impl\Service\Security;

use Doctrine\DBAL\Connection;
use Firebase\JWT\JWT;
use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Table\App\Token as AppToken;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception\UnauthorizedException;
use PSX\OAuth2\Exception\InvalidScopeException;

/**
 * TokenValidator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class TokenValidator
{
    private Connection $connection;
    private string $projectKey;
    private Repository\AppInterface $appRepository;
    private Repository\UserInterface $userRepository;

    public function __construct(Connection $connection, ConfigInterface $config, Repository\AppInterface $appRepository, Repository\UserInterface $userRepository)
    {
        $this->connection = $connection;
        $this->projectKey = $config->get('fusio_project_key');
        $this->appRepository = $appRepository;
        $this->userRepository = $userRepository;
    }

    public function assertAuthorization(string $requestMethod, ?string $authorization, Context $context): bool
    {
        if ($requestMethod === 'OPTIONS') {
            $needsAuth = false;
        } else {
            $needsAuth = $context->getOperation()->getPublic() !== 1;
        }

        $requestMethod = $requestMethod == 'HEAD' ? 'GET' : $requestMethod;

        // authorization is required if the method is not public. In case we get
        // a header from the client we also check the token so that the client
        // gets maybe another rate limit
        if ($needsAuth || !empty($authorization)) {
            $parts       = explode(' ', $authorization ?? '', 2);
            $type        = $parts[0] ?? null;
            $accessToken = $parts[1] ?? null;

            $params = array(
                'realm' => 'Fusio',
            );

            if ($type === 'Bearer' && !empty($accessToken)) {
                try {
                    $token = $this->getToken($accessToken, $context->getOperation()->getId(), $requestMethod);
                } catch (\UnexpectedValueException $e) {
                    throw new UnauthorizedException($e->getMessage(), 'Bearer', $params);
                }

                if ($token instanceof Model\Token) {
                    $app = $this->appRepository->get($token->getAppId());
                    if ($app !== null) {
                        $context->setApp($app);
                    }

                    $user = $this->userRepository->get($token->getUserId());
                    if ($user !== null) {
                        $context->setUser($user);
                    }

                    $context->setToken($token);
                } else {
                    throw new UnauthorizedException('Invalid access token', 'Bearer', $params);
                }
            } else {
                throw new UnauthorizedException('Missing authorization header', 'Bearer', $params);
            }
        } else {
            $app = new Model\App(true, 0, 0, 0, '', '', '', [], []);
            $user = new Model\User(true, 0, 0, 0, 0, '', '', 0);
            $token = new Model\Token(0, 0, 0, [], '', '');

            $context->setApp($app);
            $context->setUser($user);
            $context->setToken($token);
        }

        return true;
    }

    private function getToken(string $token, int $operationId, string $requestMethod): ?Model\Token
    {
        // @TODO in the latest version we only issue JWTs so in the next major release we can always decode the token
        if (strpos($token, '.') !== false) {
            JWT::decode($token, $this->projectKey);
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

        $accessToken = $this->connection->fetchAssociative($sql, array(
            'token'  => $token,
            'status' => AppToken::STATUS_ACTIVE,
            'now'    => $now->format($this->connection->getDatabasePlatform()->getDateTimeFormatString()),
        ));

        if (empty($accessToken)) {
            return null;
        }

        // these are the scopes which are assigned to the token
        $entitledScopes = explode(',', $accessToken['scope']);

        // if the user has a global scope like backend or consumer replace them with all sub scopes
        $entitledScopes = $this->substituteGlobalScopes($entitledScopes);

        // get all scopes which are assigned to this route
        $sql = '    SELECT scope.name,
                           scope_operation.allow,
                           scope_operation.methods
                      FROM fusio_scope_operation scope_operation
                INNER JOIN fusio_scope scope
                        ON scope.id = scope_operation.scope_id
                     WHERE scope_operation.operation_id = :operation';

        $availableScopes = $this->connection->fetchAllAssociative($sql, array('operation' => $operationId));

        // now we check whether the assigned scopes are allowed to access this route. We must have at least one scope
        // which explicit allows the request
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
            return new Model\Token(
                $accessToken['id'],
                $accessToken['app_id'],
                $accessToken['user_id'],
                $entitledScopes,
                $accessToken['expire'],
                $accessToken['date']
            );
        } else {
            throw new InvalidScopeException('Access to this resource is not in the scope of the provided token');
        }
    }

    /**
     * If the user has as entitled scope a global scope like backend or consumer he has the right to access every sub
     * scope, so we add them to the entitled scopes
     */
    private function substituteGlobalScopes(array $entitledScopes): array
    {
        $scopes = $entitledScopes;
        foreach ($entitledScopes as $scope) {
            if (strpos($scope, '.') === false) {
                $sql = 'SELECT scope.name
                          FROM fusio_scope scope
                         WHERE scope.name LIKE :name';
                $result = $this->connection->fetchAllAssociative($sql, ['name' => $scope . '.%']);
                foreach ($result as $row) {
                    $scopes[] = $row['name'];
                }
            }
        }

        return array_unique($scopes);
    }
}
