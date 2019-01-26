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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;

/**
 * Login
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Login
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var \Fusio\Impl\Service\App\Token
     */
    protected $appTokenService;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\App\Token $appTokenService
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Service\User $userService, Service\App\Token $appTokenService, Config $config)
    {
        $this->userService     = $userService;
        $this->appTokenService = $appTokenService;
        $this->config          = $config;
    }

    public function login($name, $password, array $scopes = null)
    {
        $userId = $this->userService->authenticateUser($name, $password, [Table\User::STATUS_ADMINISTRATOR, Table\User::STATUS_CONSUMER]);
        if ($userId > 0) {
            if (empty($scopes)) {
                $scopes = $this->userService->getAvailableScopes($userId);
            } else {
                $scopes = $this->userService->getValidScopes($userId, $scopes);
            }

            // @TODO this is the consumer app. Probably we need a better way to
            // define this id
            $appId = 2;

            $token = $this->appTokenService->generateAccessToken(
                $appId,
                $userId,
                $scopes,
                isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                new \DateInterval($this->config->get('fusio_expire_consumer'))
            );

            return $token->getAccessToken();
        }

        return null;
    }
}
