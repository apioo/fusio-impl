<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\Authorize_Request;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use PSX\Uri\Uri;
use PSX\Uri\Url;

/**
 * Authorize
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Authorize
{
    private Service\App\Token $appTokenService;
    private Service\Scope $scopeService;
    private Service\App\Code $appCodeService;
    private Table\App $appTable;
    private Table\User\Grant $userGrantTable;
    private Config $config;

    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Service\App\Code $appCodeService, Table\App $appTable, Table\User\Grant $userGrantTable, Config $config)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appCodeService  = $appCodeService;
        $this->appTable        = $appTable;
        $this->userGrantTable  = $userGrantTable;
        $this->config          = $config;
    }

    public function authorize(int $userId, Authorize_Request $request): array
    {
        // response type
        if (!in_array($request->getResponseType(), ['code', 'token'])) {
            throw new StatusCode\BadRequestException('Invalid response type');
        }

        // client id
        $app = $this->getApp($request->getClientId());

        // redirect uri
        $redirectUri = $request->getRedirectUri();
        if (!empty($redirectUri)) {
            $redirectUri = new Uri($redirectUri);

            if (!$redirectUri->isAbsolute()) {
                throw new StatusCode\BadRequestException('Redirect uri must be an absolute url');
            }

            if (!in_array($redirectUri->getScheme(), ['http', 'https'])) {
                throw new StatusCode\BadRequestException('Invalid redirect uri scheme');
            }

            $url = $app['url'];
            if (!empty($url)) {
                $url = new Url($url);
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
        $scopes = $this->scopeService->getValidScopes($request->getScope(), (int) $app['id'], (int) $userId);
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No valid scopes provided');
        }

        // save the decision of the user. We save the decision so that it is
        // possible for the user to revoke the access later on
        $this->saveUserDecision($userId, $app['id'], $request->getAllow());

        $state = $request->getState();
        if ($request->getAllow()) {
            if ($request->getResponseType() == 'token') {
                // redirect uri is required for token types
                if (!$redirectUri instanceof Uri) {
                    throw new StatusCode\BadRequestException('Redirect uri is required');
                }

                // generate access token
                $accessToken = $this->appTokenService->generateAccessToken(
                    $app['id'],
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
                    $app['id'],
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

    protected function saveUserDecision($userId, $appId, $allow)
    {
        $condition = new Condition();
        $condition->equals('user_id', $userId);
        $condition->equals('app_id', $appId);

        $userApp = $this->userGrantTable->findOneBy($condition);

        if (empty($userApp)) {
            $record = new Table\Generated\UserGrantRow([
                'user_id' => $userId,
                'app_id'  => $appId,
                'allow'   => $allow ? 1 : 0,
                'date'    => new \DateTime(),
            ]);

            $this->userGrantTable->create($record);
        } else {
            $record = new Table\Generated\UserGrantRow([
                'id'      => $userApp['id'],
                'user_id' => $userId,
                'app_id'  => $appId,
                'allow'   => $allow ? 1 : 0,
                'date'    => new \DateTime(),
            ]);

            $this->userGrantTable->update($record);
        }
    }
    
    private function getApp(string $clientId): Table\Generated\AppRow
    {
        $condition = new Condition();
        $condition->equals('app_key', $clientId);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->findOneBy($condition);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Unknown client id');
        }

        return $app;
    }
}
