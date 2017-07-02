<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
     * @var \Fusio\Impl\Service\User\TokenIssuer
     */
    protected $tokenIssuer;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\User\TokenIssuer $tokenIssuer
     */
    public function __construct(Service\User $userService, TokenIssuer $tokenIssuer)
    {
        $this->userService = $userService;
        $this->tokenIssuer = $tokenIssuer;
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

            return $this->tokenIssuer->createToken($userId, $scopes);
        }

        return null;
    }
}
