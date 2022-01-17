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

namespace Fusio\Impl\Service\Consumer;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Model\Backend\User_Attributes;
use Fusio\Model\Backend\User_Update;
use Fusio\Model\Consumer\User_Account;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class User
{
    private Service\User $userService;

    public function __construct(Service\User $userService)
    {
        $this->userService = $userService;
    }

    public function update(User_Account $account, UserContext $context): int
    {
        $attributes = new User_Attributes();
        foreach ($account->getAttributes() ?? [] as $key => $value) {
            $attributes->setProperty($key, $value);
        }

        $backendUser = new User_Update();
        $backendUser->setEmail($account->getEmail());
        $backendUser->setAttributes($attributes);

        return $this->userService->update($context->getUserId(), $backendUser, $context);
    }
}
