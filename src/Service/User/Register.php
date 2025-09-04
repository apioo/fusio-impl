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
readonly class Register
{
    public function __construct(
        private Service\User $userService,
        private Captcha $captchaService,
        private Token $tokenService,
        private Mailer $mailerService,
        private Service\Config $configService,
        private Table\User $userTable,
        private Table\Role $roleTable,
        private Service\System\FrameworkConfig $frameworkConfig,
    ) {
    }

    public function register(UserRegister $register, UserContext $context): int
    {
        if (!$this->frameworkConfig->isRegistrationEnabled()) {
            throw new StatusCode\ServiceUnavailableException('User registration is not enabled');
        }

        $this->captchaService->assertCaptcha($register->getCaptcha());

        // determine initial user status
        $status = Table\User::STATUS_DISABLED;
        $approval = $this->configService->getValue('user_approval');
        if (!$approval) {
            $status = Table\User::STATUS_ACTIVE;
        }

        $role = $this->roleTable->findOneByTenantAndName($context->getTenantId(), $this->configService->getValue('role_default'));
        if (!$role instanceof Table\Generated\RoleRow) {
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

        $userId = $this->userService->create($user, $context);

        // send activation mail
        if ($approval) {
            $this->sendActivationMail((string) $userId, $context);
        }

        return $userId;
    }

    public function sendActivationMail(string $userId, UserContext $context): void
    {
        $user = $this->userTable->findOneByIdentifier($context->getTenantId(), $userId);
        if (!$user instanceof Table\Generated\UserRow) {
            throw new StatusCode\NotFoundException('Provided user does not exist');
        }

        $token = $this->tokenService->generateToken($context->getTenantId(), $userId);

        $email = $user->getEmail();
        if (empty($email)) {
            throw new StatusCode\BadRequestException('Provided user has no email configured');
        }

        $this->mailerService->sendActivationMail($user->getName(), $email, $token);
    }
}
