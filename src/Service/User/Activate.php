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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\User_Activate;
use PSX\Http\Exception as StatusCode;

/**
 * Activate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Activate
{
    private Service\User $userService;
    private Service\User\Token $tokenService;

    public function __construct(Service\User $userService, Service\User\Token $tokenService)
    {
        $this->userService  = $userService;
        $this->tokenService = $tokenService;
    }

    public function activate(User_Activate $activate): void
    {
        $userId = $this->tokenService->getUser($activate->getToken());
        if (!empty($userId)) {
            $this->userService->changeStatus($userId, Table\User::STATUS_ACTIVE, UserContext::newAnonymousContext());
        } else {
            throw new StatusCode\BadRequestException('Token is expired');
        }
    }
}
