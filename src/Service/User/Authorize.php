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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\AuthorizeRequest;
use PSX\DateTime\LocalDateTime;
use PSX\Framework\Config\ConfigInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use PSX\Uri\Uri;
use PSX\Uri\Url;

/**
 * Authorize
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Authorize
{
    private Service\App\Token $appTokenService;
    private Service\Scope $scopeService;
    private Service\App\Code $appCodeService;
    private Table\App $appTable;
    private Table\User\Grant $userGrantTable;
    private ConfigInterface $config;

    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Service\App\Code $appCodeService, Table\App $appTable, Table\User\Grant $userGrantTable, ConfigInterface $config)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appCodeService  = $appCodeService;
        $this->appTable        = $appTable;
        $this->userGrantTable  = $userGrantTable;
        $this->config          = $config;
    }

    public function authorize(int $userId, AuthorizeRequest $request): array
    {
        // response type
        if (!in_array($request->getResponseType(), ['code', 'token'])) {
            throw new StatusCode\BadRequestException('Invalid response type');
        }

        // client id
        $clientId = $request->getClientId();
        if (empty($clientId)) {
            throw new StatusCode\BadRequestException('No client id provided');
        }

        $app = $this->getApp($clientId);

        // redirect uri
        $redirectUri = $request->getRedirectUri();
        if (!empty($redirectUri)) {
            $redirectUri = Uri::parse($redirectUri);

            if (!$redirectUri->isAbsolute()) {
                throw new StatusCode\BadRequestException('Redirect uri must be an absolute url');
            }

            if (!in_array($redirectUri->getScheme(), ['http', 'https'])) {
                throw new StatusCode\BadRequestException('Invalid redirect uri scheme');
            }

            $url = $app->getUrl();
            if (!empty($url)) {
                $url = Url::parse($url);
                if ($url->getHost() != $redirectUri->getHost()) {
                    throw new StatusCode\BadRequestException('Redirect uri must have the same host as the app url');
                }
            } else {
                throw new StatusCode\BadRequestException('App has no url configured');
            }
        } else {
            $redirectUri = null;
        }

        // scopes
        $scopes = $this->scopeService->getValidScopes($request->getScope() ?? '', $app->getId(), $userId);
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No valid scopes provided');
        }

        // save the decision of the user. We save the decision so that it is
        // possible for the user to revoke the access later on
        $this->saveUserDecision($userId, $app->getId(), $request->getAllow() ?? false);

        $state = $request->getState();
        if ($request->getAllow()) {
            if ($request->getResponseType() == 'token') {
                // redirect uri is required for token types
                if (!$redirectUri instanceof Uri) {
                    throw new StatusCode\BadRequestException('Redirect uri is required');
                }

                // generate access token
                $accessToken = $this->appTokenService->generateAccessToken(
                    $app->getId(),
                    $userId,
                    $scopes,
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    new \DateInterval($this->config->get('fusio_expire_token')),
                    $state
                );

                $parameters = array_filter([
                    'access_token' => $accessToken->getAccessToken(),
                    'token_type' => $accessToken->getTokenType(),
                    'expires_in' => $accessToken->getExpiresIn(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'scope' => $accessToken->getScope(),
                    'state' => $accessToken->getState(),
                ]);

                $redirectUri = $redirectUri->withFragment(http_build_query($parameters, '', '&'))->toString();

                return [
                    'type' => 'token',
                    'token' => $parameters,
                    'redirectUri' => $redirectUri,
                ];
            } else {
                // generate code which can be later exchanged by the app with an
                // access token
                $code = $this->appCodeService->generateCode(
                    $app->getId(),
                    $userId,
                    $redirectUri,
                    $scopes
                );

                if ($redirectUri instanceof Uri) {
                    $parameters = array();
                    $parameters['code'] = $code;
                    $parameters['state'] = $state;

                    $redirectUri = $redirectUri->withParameters($parameters)->toString();
                } else {
                    $redirectUri = '#';
                }

                return [
                    'type' => 'code',
                    'code' => $code,
                    'redirectUri' => $redirectUri,
                ];
            }
        } else {
            // @TODO delete all previously issued tokens for this app?

            if ($redirectUri instanceof Uri) {
                $parameters = array();
                $parameters['error'] = 'access_denied';

                if (!empty($state)) {
                    $parameters['state'] = $state;
                }

                if ($request->getResponseType() == 'token') {
                    $redirectUri = $redirectUri->withFragment(http_build_query($parameters, '', '&'))->toString();
                } else {
                    $redirectUri = $redirectUri->withParameters($parameters)->toString();
                }
            } else {
                $redirectUri = '#';
            }

            return [
                'type' => 'access_denied',
                'redirectUri' => $redirectUri
            ];
        }
    }

    protected function saveUserDecision(int $userId, int $appId, bool $allow): void
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
        $condition->equals(Table\Generated\AppTable::COLUMN_APP_KEY, $clientId);
        $condition->equals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Unknown client id');
        }

        return $app;
    }
}
