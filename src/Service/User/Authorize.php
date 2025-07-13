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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\AuthorizeRequest;
use Fusio\Model\Consumer\AuthorizeResponse;
use PSX\DateTime\LocalDateTime;
use PSX\OAuth2\Exception\AccessDeniedException;
use PSX\OAuth2\Exception\ErrorExceptionAbstract;
use PSX\OAuth2\Exception\InvalidRequestException;
use PSX\OAuth2\Exception\ServerErrorException;
use PSX\OAuth2\Exception\UnsupportedResponseTypeException;
use PSX\Sql\Condition;
use PSX\Uri\Exception\InvalidFormatException;
use PSX\Uri\Url;

/**
 * Authorize
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Authorize
{
    public function __construct(
        private Service\Scope $scopeService,
        private Service\App\Code $appCodeService,
        private Table\App $appTable,
        private Table\App\Scope $appScopeTable,
        private Table\Token $tokenTable,
        private Table\User\Grant $userGrantTable,
        private Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function authorize(int $userId, AuthorizeRequest $request): AuthorizeResponse
    {
        $url = null;

        try {
            $url = $this->parseUrl($request->getRedirectUri());

            if ($request->getResponseType() !== 'code') {
                throw new UnsupportedResponseTypeException('Invalid response type');
            }

            $clientId = $request->getClientId();
            if (empty($clientId)) {
                throw new InvalidRequestException('No client id provided');
            }

            $app = $this->getApp($clientId);

            if ($url instanceof Url) {
                $appUrl = $app->getUrl();
                if (!empty($appUrl)) {
                    $appUrl = Url::parse($appUrl);
                    if (!str_ends_with($url->getHost(), $appUrl->getHost())) {
                        throw new InvalidRequestException('Redirect uri must have the same host as the app url');
                    }
                } else {
                    throw new ServerErrorException('App has no url configured');
                }
            }

            $scope = $request->getScope();
            if (!empty($scope)) {
                $scopes = $this->scopeService->getValidScopes($app->getTenantId(), $request->getScope() ?? '', $app->getId(), $userId);
            } else {
                $scopes = Table\Scope::getNames($this->appScopeTable->getAvailableScopes($this->frameworkConfig->getTenantId(), $app->getId()));
            }

            if (!$request->getAllow()) {
                $this->tokenTable->removeAllTokensFromAppAndUser($this->frameworkConfig->getTenantId(), $app->getId(), $userId);

                throw new AccessDeniedException('The access was denied by the user');
            }

            // save the decision of the user so that it is possible for the user to revoke the access later on
            $this->saveUserDecision($userId, $app->getId(), true);

            // generate code which can be later exchanged by the app with an access token
            $code = $this->appCodeService->generateCode(
                $app->getId(),
                $userId,
                $url?->toString(),
                $scopes
            );

            if ($url instanceof Url) {
                $parameters = [];
                $parameters['code'] = $code;
                $parameters['state'] = $request->getState();

                $url = $url->withParameters($parameters)->toString();
            }

            $response = new AuthorizeResponse();
            $response->setType('code');
            $response->setCode($code);
            $response->setState($request->getState());
            $response->setRedirectUri($url);
        } catch (ErrorExceptionAbstract $e) {
            if ($url instanceof Url) {
                $parameters = [];
                $parameters['error'] = $e->getType();
                $parameters['error_description'] = $e->getMessage();
                $parameters['state'] = $request->getState();

                $url = $url->withParameters($parameters)->toString();
            }

            $response = new AuthorizeResponse();
            $response->setType($e->getType());
            $response->setError($e->getMessage());
            $response->setState($request->getState());
            $response->setRedirectUri($url);
        }

        return $response;
    }

    private function parseUrl(?string $url): ?Url
    {
        if (empty($url)) {
            return null;
        }

        try {
            $redirectUri = Url::parse($url);
        } catch (InvalidFormatException $e) {
            throw new InvalidRequestException('Provided an invalid redirect uri', previous: $e);
        }

        if (!in_array($redirectUri->getScheme(), ['http', 'https'])) {
            throw new InvalidRequestException('Provided an invalid redirect uri');
        }

        return $redirectUri;
    }

    private function saveUserDecision(int $userId, int $appId, bool $allow): void
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\UserGrantTable::COLUMN_USER_ID, $userId);
        $condition->equals(Table\Generated\UserGrantTable::COLUMN_APP_ID, $appId);

        $existing = $this->userGrantTable->findOneBy($condition);
        if (empty($existing)) {
            $row = new Table\Generated\UserGrantRow();
            $row->setUserId($userId);
            $row->setAppId($appId);
            $row->setAllow($allow ? 1 : 0);
            $row->setDate(LocalDateTime::now());
            $this->userGrantTable->create($row);
        } else {
            $existing->setUserId($userId);
            $existing->setAppId($appId);
            $existing->setAllow($allow ? 1 : 0);
            $existing->setDate(LocalDateTime::now());
            $this->userGrantTable->update($existing);
        }
    }

    private function getApp(string $clientId): Table\Generated\AppRow
    {
        $condition = Condition::withAnd();
        $condition->equals(Table\Generated\AppTable::COLUMN_TENANT_ID, $this->frameworkConfig->getTenantId());
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_KEY, $clientId);
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
        if (!$app instanceof Table\Generated\AppRow) {
            throw new InvalidRequestException('Provided an invalid client id');
        }

        return $app;
    }
}
