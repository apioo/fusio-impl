<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
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
readonly class Activate
{
    public function __construct(
        private Service\User $userService,
        private Service\User\Token $tokenService,
        private Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function activate(UserActivate $activate, UserContext $context): void
    {
        if (!$this->frameworkConfig->isRegistrationEnabled()) {
            throw new StatusCode\ServiceUnavailableException('User registration is not enabled');
        }

        $token = $activate->getToken();
        if (empty($token)) {
            throw new StatusCode\BadRequestException('No token provided');
        }

        $userId = $this->tokenService->getUser($context->getTenantId(), $token);
        if (!empty($userId)) {
            $this->userService->changeStatus($userId, Table\User::STATUS_ACTIVE, $context);
        } else {
            throw new StatusCode\BadRequestException('Token is expired');
        }
    }
}
