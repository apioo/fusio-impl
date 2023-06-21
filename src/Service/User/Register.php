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
use Fusio\Model\Backend\UserCreate;
use Fusio\Model\Consumer\UserRegister;
use PSX\Http\Exception as StatusCode;

/**
 * Register
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Register
{
    private Service\User $userService;
    private Service\User\Captcha $captchaService;
    private Service\User\Token $tokenService;
    private Service\User\Mailer $mailerService;
    private Service\Config $configService;
    private Table\Role $roleTable;

    public function __construct(Service\User $userService, Captcha $captchaService, Token $tokenService, Mailer $mailerService, Service\Config $configService, Table\Role $roleTable)
    {
        $this->userService    = $userService;
        $this->captchaService = $captchaService;
        $this->tokenService   = $tokenService;
        $this->mailerService  = $mailerService;
        $this->configService  = $configService;
        $this->roleTable      = $roleTable;
    }

    public function register(UserRegister $register): int
    {
        $this->captchaService->assertCaptcha($register->getCaptcha());

        // determine initial user status
        $status   = Table\User::STATUS_DISABLED;
        $approval = $this->configService->getValue('user_approval');
        if (!$approval) {
            $status = Table\User::STATUS_ACTIVE;
        }

        $role = $this->roleTable->findOneByName($this->configService->getValue('role_default'));
        if (empty($role)) {
            throw new StatusCode\InternalServerErrorException('Invalid default role configured');
        }

        $name = $register->getName();
        if (empty($name)) {
            throw new StatusCode\BadRequestException('No username was provided');
        }

        $email = $register->getEmail();
        if (empty($email)) {
            throw new StatusCode\BadRequestException('No email was provided');
        }

        $password = $register->getPassword();
        if (empty($password)) {
            throw new StatusCode\BadRequestException('No password was provided');
        }

        $user = new UserCreate();
        $user->setRoleId($role->getId());
        $user->setStatus($status);
        $user->setName($name);
        $user->setEmail($email);
        $user->setPassword($password);

        $userId = $this->userService->create($user, UserContext::newAnonymousContext());

        // send activation mail
        if ($approval) {
            $token = $this->tokenService->generateToken($userId);

            $this->mailerService->sendActivationMail($name, $email, $token);
        }

        return $userId;
    }
}
