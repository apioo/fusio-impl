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

namespace Fusio\Impl\Authorization;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Oauth2\Credentials;
use PSX\Framework\Oauth2\GrantType\RefreshTokenAbstract;
use PSX\Oauth2\Authorization\Exception\ServerErrorException;
use PSX\Sql\Condition;

/**
 * RefreshToken
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RefreshToken extends RefreshTokenAbstract
{
    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    protected $appTokenService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    protected $appTable;

    /**
     * @var string
     */
    protected $expireApp;

    /**
     * @var string
     */
    protected $expireRefresh;

    /**
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \Fusio\Impl\Table\App $appTable
     * @param string $expireApp
     * @param string $expireRefresh
     */
    public function __construct(Service\App\Token $appTokenService, Table\App $appTable, $expireApp, $expireRefresh = null)
    {
        $this->appTokenService = $appTokenService;
        $this->appTable        = $appTable;
        $this->expireApp       = $expireApp;
        $this->expireRefresh   = $expireRefresh ?: 'P3D';
    }

    /**
     * @param \PSX\Framework\Oauth2\Credentials $credentials
     * @param string $refreshToken
     * @param string $scope
     * @return \PSX\Oauth2\AccessToken
     */
    protected function generate(Credentials $credentials, $refreshToken, $scope)
    {
        $condition = new Condition();
        $condition->equals('app_key', $credentials->getClientId());
        $condition->equals('app_secret', $credentials->getClientSecret());
        $condition->equals('status', Table\App::STATUS_ACTIVE);

        $app = $this->appTable->getOneBy($condition);
        if (empty($app)) {
            throw new ServerErrorException('Unknown credentials');
        }

        // refresh access token
        return $this->appTokenService->refreshAccessToken(
            $app['id'],
            $refreshToken,
            isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            new \DateInterval($this->expireApp),
            new \DateInterval($this->expireRefresh)
        );
    }
}
