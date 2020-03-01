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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
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
 * @link    http://fusio-project.org
 */
class Authorize
{
    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    protected $appTokenService;

    /**
     * @var \Fusio\Impl\Service\Scope
     */
    protected $scopeService;

    /**
     * @var \Fusio\Impl\Service\App\Code
     */
    protected $appCodeService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    protected $appTable;

    /**
     * @var \Fusio\Impl\Table\User\Grant
     */
    protected $userGrantTable;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Service\Scope $scopeService
     * @param \Fusio\Impl\Service\App\Code $appCodeService
     * @param \Fusio\Impl\Table\App $appTable
     * @param \Fusio\Impl\Table\User\Grant $userGrantTable
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Service\App\Token $appTokenService, Service\Scope $scopeService, Service\App\Code $appCodeService, Table\App $appTable, Table\User\Grant $userGrantTable, Config $config)
    {
        $this->appTokenService = $appTokenService;
        $this->scopeService    = $scopeService;
        $this->appCodeService  = $appCodeService;
        $this->appTable        = $appTable;
        $this->userGrantTable  = $userGrantTable;
        $this->config          = $config;
    }

    public function authorize($userId, $responseType, $clientId, $redirectUri, $scope, $state, $allow)
    {
        // response type
        if (!in_array($responseType, ['code', 'token'])) {
            throw new StatusCode\BadRequestException('Invalid response type');
        }

        // client id
        $app = $this->getApp($clientId);

        // redirect uri
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
        $scopes = $this->scopeService->getValidScopes($app['id'], $userId, $scope);
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('No valid scopes provided');
        }

        // save the decision of the user. We save the decision so that it is
        // possible for the user to revoke the access later on
        $this->saveUserDecision($userId, $app['id'], $allow);

        if ($allow) {
            if ($responseType == 'token') {
                // check whether implicit grant is allowed
                if ($this->config['fusio_grant_implicit'] !== true) {
                    throw new StatusCode\BadRequestException('Token response type is not supported');
                }

                // redirect uri is required for token types
                if (!$redirectUri instanceof Uri) {
                    throw new StatusCode\BadRequestException('Redirect uri is required');
                }

                // generate access token
                $accessToken = $this->appTokenService->generateAccessToken(
                    $app['id'],
                    $userId,
                    $scopes,
                    isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                    new \DateInterval($this->config->get('fusio_expire_implicit'))
                );

                $parameters = $accessToken->getProperties();

                if (!empty($state)) {
                    $parameters['state'] = $state;
                }

                $redirectUri = $redirectUri->withFragment(http_build_query($parameters, '', '&'))->toString();

                return [
                    'type' => 'token',
                    'token' => $accessToken,
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

                if ($responseType == 'token') {
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

        $userApp = $this->userGrantTable->getOneBy($condition);

        if (empty($userApp)) {
            $this->userGrantTable->create([
                'user_id' => $userId,
                'app_id'  => $appId,
                'allow'   => $allow ? 1 : 0,
                'date'    => new \DateTime(),
            ]);
        } else {
            $this->userGrantTable->update([
                'id'      => $userApp['id'],
                'user_id' => $userId,
                'app_id'  => $appId,
                'allow'   => $allow ? 1 : 0,
                'date'    => new \DateTime(),
            ]);
        }
    }
    
    private function getApp($clientId)
    {
        $condition = new Condition();
        $condition->equals('app_key', $clientId);
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->getOneBy($condition);
        if (empty($app)) {
            throw new StatusCode\BadRequestException('Unknown client id');
        }

        return $app;
    }
}
