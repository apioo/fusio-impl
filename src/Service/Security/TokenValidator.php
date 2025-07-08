<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

namespace Fusio\Impl\Service\Security;

use Fusio\Engine\Model;
use Fusio\Engine\Repository;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Service\System\FrameworkConfig;
use Fusio\Impl\Table;
use PSX\Http\Exception\UnauthorizedException;
use PSX\OAuth2\Exception\InvalidScopeException;

/**
 * TokenValidator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TokenValidator
{
    private JsonWebToken $jsonWebToken;
    private Repository\AppInterface $appRepository;
    private Repository\UserInterface $userRepository;
    private Table\Token $tokenTable;
    private Table\Scope $scopeTable;
    private FrameworkConfig $frameworkConfig;

    public function __construct(JsonWebToken $jsonWebToken, Repository\AppInterface $appRepository, Repository\UserInterface $userRepository, Table\Token $tokenTable, Table\Scope $scopeTable, FrameworkConfig $frameworkConfig)
    {
        $this->jsonWebToken = $jsonWebToken;
        $this->appRepository = $appRepository;
        $this->userRepository = $userRepository;
        $this->tokenTable = $tokenTable;
        $this->scopeTable = $scopeTable;
        $this->frameworkConfig = $frameworkConfig;
    }

    public function assertAuthorization(?string $authorization, Context $context): bool
    {
        $needsAuth = $context->getOperation()->getPublic() !== 1;

        if ($needsAuth || !empty($authorization)) {
            $parts = explode(' ', $authorization ?? '', 2);
            $type = $parts[0] ?? null;
            $accessToken = $parts[1] ?? null;

            $params = [
                'realm' => 'Fusio',
            ];

            if (empty($type)) {
                throw new UnauthorizedException('Missing authorization header', 'Bearer', $params);
            }

            if ($type !== 'Bearer') {
                throw new UnauthorizedException('Invalid authorization type', 'Bearer', $params);
            }

            if (empty($accessToken)) {
                throw new UnauthorizedException('No authorization token was provided', 'Bearer', $params);
            }

            try {
                $token = $this->getToken($accessToken, $context->getOperation()->getId());
            } catch (\UnexpectedValueException $e) {
                throw new UnauthorizedException($e->getMessage(), 'Bearer', $params);
            }

            if (!$token instanceof Model\Token) {
                throw new UnauthorizedException('Invalid access token', 'Bearer', $params);
            }

            $appId = $token->getAppId();
            if ($appId !== null && $app = $this->appRepository->get($appId)) {
                $context->setApp($app);
            } else {
                $context->setApp(new Model\AppAnonymous());
            }

            $user = $this->userRepository->get($token->getUserId());
            if ($user !== null) {
                $context->setUser($user);
            } else {
                $context->setUser(new Model\UserAnonymous());
            }

            $context->setToken($token);
        } else {
            $context->setApp(new Model\AppAnonymous());
            $context->setUser(new Model\UserAnonymous());
            $context->setToken(new Model\TokenAnonymous());
        }

        return true;
    }

    private function getToken(string $token, int $operationId): ?Model\Token
    {
        // @TODO in the latest version we only issue JWTs so in the next major release we can always decode the token
        if (str_contains($token, '.')) {
            $this->jsonWebToken->decode($token);
        }

        $accessToken = $this->tokenTable->findByAccessToken($this->frameworkConfig->getTenantId(), $token);
        if (empty($accessToken)) {
            return null;
        }

        // these are the scopes which are assigned to the token
        $entitledScopes = explode(',', $accessToken['scope']);

        // if the user has a global scope like backend or consumer replace them with all sub scopes
        $entitledScopes = $this->substituteGlobalScopes($this->frameworkConfig->getTenantId(), $entitledScopes);

        // get all scopes which are assigned to this route
        $availableScopes = $this->scopeTable->findByOperationId($this->frameworkConfig->getTenantId(), $operationId);

        // now we check whether the assigned scopes are allowed to access this route. We must have at least one scope
        // which explicit allows the request
        $isAllowed = false;

        foreach ($entitledScopes as $entitledScope) {
            foreach ($availableScopes as $scope) {
                if ($scope[Table\Generated\ScopeTable::COLUMN_NAME] == $entitledScope && $scope[Table\Generated\ScopeOperationTable::COLUMN_ALLOW] == 1) {
                    $isAllowed = true;
                    break 2;
                }
            }
        }

        if ($isAllowed) {
            return new Model\Token(
                $accessToken[Table\Generated\TokenTable::COLUMN_ID],
                $accessToken[Table\Generated\TokenTable::COLUMN_APP_ID],
                $accessToken[Table\Generated\TokenTable::COLUMN_USER_ID],
                $entitledScopes,
                $accessToken[Table\Generated\TokenTable::COLUMN_EXPIRE],
                $accessToken[Table\Generated\TokenTable::COLUMN_DATE]
            );
        } else {
            throw new InvalidScopeException('Access to this operation is not in the scope of the provided token');
        }
    }

    /**
     * If the user has as entitled scope a global scope like backend or consumer he has the right to access every sub
     * scope, so we add them to the entitled scopes
     */
    private function substituteGlobalScopes(?string $tenantId, array $entitledScopes): array
    {
        $scopes = $entitledScopes;
        foreach ($entitledScopes as $scope) {
            if (!str_contains($scope, '.')) {
                $subScopes = Table\Scope::getNames($this->scopeTable->findSubScopes($tenantId, $scope));
                foreach ($subScopes as $subScope) {
                    $scopes[] = $subScope;
                }
            }
        }

        return array_unique($scopes);
    }
}
