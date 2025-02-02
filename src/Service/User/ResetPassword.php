<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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
use Fusio\Model\Consumer\UserEmail;
use Fusio\Model\Consumer\UserPasswordReset;
use PSX\Http\Exception as StatusCode;

/**
 * ResetPassword
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ResetPassword
{
    private Service\User\Captcha $captchaService;
    private Service\User\Token $tokenService;
    private Service\User\Mailer $mailerService;
    private Table\User $userTable;

    public function __construct(Captcha $captchaService, Token $tokenService, Mailer $mailerService, Table\User $userTable)
    {
        $this->mailerService = $mailerService;
        $this->captchaService = $captchaService;
        $this->tokenService = $tokenService;
        $this->userTable = $userTable;
    }

    public function resetPassword(UserEmail $reset, UserContext $context): void
    {
        $this->captchaService->assertCaptcha($reset->getCaptcha());

        $email = $reset->getEmail();
        if (empty($email)) {
            throw new StatusCode\BadRequestException('No email was provided');
        }

        $user = $this->userTable->findOneByTenantAndEmail($context->getTenantId(), $email);
        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        $identityId = $user->getIdentityId();
        if (!empty($identityId)) {
            throw new StatusCode\BadRequestException('Provided user is not a local user');
        }

        $email = $user->getEmail();
        if (empty($email)) {
            throw new StatusCode\BadRequestException('Provided user has no assigned email');
        }

        // set onetime token for the user
        $token = $this->tokenService->generateToken($context->getTenantId(), $user->getId());

        // send reset mail
        $this->mailerService->sendResetPasswordMail($user->getName(), $email, $token);
    }

    public function changePassword(UserPasswordReset $reset, UserContext $context): void
    {
        $token = $reset->getToken();
        if (empty($token)) {
            throw new StatusCode\NotFoundException('No token was provided');
        }

        $userId = $this->tokenService->getUser($context->getTenantId(), $token);
        if (empty($userId)) {
            throw new StatusCode\NotFoundException('Invalid token provided');
        }

        $newPassword = $reset->getNewPassword();
        if (empty($newPassword)) {
            throw new StatusCode\BadRequestException('Provided no new password');
        }

        $result = $this->userTable->changePassword($context->getTenantId(), $userId, null, $newPassword, false);
        if (!$result) {
            throw new StatusCode\BadRequestException('Could not change password');
        }
    }
}
