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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\UserActivate;
use PSX\Http\Exception as StatusCode;

/**
 * Activate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    public function activate(UserActivate $activate): void
    {
        $token = $activate->getToken();
        if (empty($token)) {
            throw new StatusCode\BadRequestException('No token provided');
        }

        $userId = $this->tokenService->getUser($token);
        if (!empty($userId)) {
            $this->userService->changeStatus($userId, Table\User::STATUS_ACTIVE, UserContext::newAnonymousContext());
        } else {
            throw new StatusCode\BadRequestException('Token is expired');
        }
    }
}
