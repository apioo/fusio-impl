<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Service\App as AppService;
use Fusio\Impl\Service\App\Code as AppCodeService;
use Fusio\Impl\Service\Scope as ScopeService;
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\AuthorizationCodeAbstract;
use PSX\Oauth2\Authorization\Exception\ServerErrorException;

/**
 * AuthorizationCode
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AuthorizationCode extends AuthorizationCodeAbstract
{
    protected $appCodeService;
    protected $scopeService;
    protected $appService;
    protected $expireApp;

    public function __construct(AppCodeService $appCodeService, ScopeService $scopeService, AppService $appService, $expireApp)
    {
        $this->appCodeService = $appCodeService;
        $this->scopeService   = $scopeService;
        $this->appService     = $appService;
        $this->expireApp      = $expireApp;
    }

    protected function generate(Credentials $credentials, $code, $redirectUri, $clientId)
    {
        $code = $this->appCodeService->getCode(
            $credentials->getClientId(),
            $credentials->getClientSecret(),
            $code,
            $redirectUri ?: ''
        );

        if (!empty($code)) {
            // check whether the code is older then 30 minutes. After that we
            // can not exchange it for an access token
            if (time() - strtotime($code['date']) > 60 * 30) {
                throw new ServerErrorException('Code is expired');
            }

            // scopes
            $scopes = $this->scopeService->getValidScopes($code['appId'], $code['userId'], $code['scope'], ['backend']);
            if (empty($scopes)) {
                throw new ServerErrorException('No valid scope given');
            }

            // generate access token
            return $this->appService->generateAccessToken(
                $code['appId'],
                $code['userId'],
                $scopes,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                new \DateInterval($this->expireApp)
            );
        } else {
            throw new ServerErrorException('Unknown credentials');
        }
    }
}
