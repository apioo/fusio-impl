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

use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\UserEmail;
use Fusio\Model\Consumer\UserPasswordReset;
use PSX\Http\Exception as StatusCode;

/**
 * ResetPassword
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ResetPassword
{
    private Service\User $userService;
    private Service\User\Captcha $captchaService;
    private Service\User\Token $tokenService;
    private Service\User\Mailer $mailerService;
    private Table\User $userTable;

    public function __construct(Service\User $userService, Captcha $captchaService, Token $tokenService, Mailer $mailerService, Table\User $userTable)
    {
        $this->userService    = $userService;
        $this->mailerService  = $mailerService;
        $this->captchaService = $captchaService;
        $this->tokenService   = $tokenService;
        $this->userTable      = $userTable;
    }

    public function resetPassword(UserEmail $reset): void
    {
        $this->captchaService->assertCaptcha($reset->getCaptcha());

        $user = $this->userTable->findOneByEmail($reset->getEmail());
        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($user->getProvider() != ProviderInterface::PROVIDER_SYSTEM) {
            throw new StatusCode\BadRequestException('Provided user is not a local user');
        }

        // set onetime token for the user
        $token = $this->tokenService->generateToken($user->getId());

        // send reset mail
        $this->mailerService->sendResetPasswordMail($user->getName(), $user->getEmail(), $token);
    }

    public function changePassword(UserPasswordReset $reset): void
    {
        $userId = $this->tokenService->getUser($reset->getToken());
        if (empty($userId)) {
            throw new StatusCode\NotFoundException('Invalid token provided');
        }

        $result = $this->userTable->changePassword($userId, null, $reset->getNewPassword(), false);
        if (!$result) {
            throw new StatusCode\BadRequestException('Could not change password');
        }
    }
}
