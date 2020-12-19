<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Consumer\Model\User_Email;
use Fusio\Impl\Consumer\Model\User_PasswordReset;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * ResetPassword
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ResetPassword
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    private $userService;

    /**
     * @var \Fusio\Impl\Service\User\Captcha
     */
    private $captchaService;

    /**
     * @var \Fusio\Impl\Service\User\Token
     */
    private $tokenService;

    /**
     * @var \Fusio\Impl\Service\User\Mailer
     */
    private $mailerService;

    /**
     * @var \Fusio\Impl\Table\User
     */
    private $userTable;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\User\Captcha $captchaService
     * @param \Fusio\Impl\Service\User\Token $tokenService
     * @param \Fusio\Impl\Service\User\Mailer $mailerService
     * @param \Fusio\Impl\Table\User $userTable
     */
    public function __construct(Service\User $userService, Captcha $captchaService, Token $tokenService, Mailer $mailerService, Table\User $userTable)
    {
        $this->userService    = $userService;
        $this->mailerService  = $mailerService;
        $this->captchaService = $captchaService;
        $this->tokenService   = $tokenService;
        $this->userTable      = $userTable;
    }

    public function resetPassword(User_Email $reset)
    {
        $this->captchaService->assertCaptcha($reset->getCaptcha());

        $user = $this->userTable->getOneByEmail($reset->getEmail());
        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($user['provider'] != ProviderInterface::PROVIDER_SYSTEM) {
            throw new StatusCode\BadRequestException('Provided user is not a local user');
        }

        // set onetime token for the user
        $token = $this->tokenService->generateToken($user['id']);

        // send reset mail
        $this->mailerService->sendResetPasswordMail($token, $user['name'], $user['email']);
    }

    public function changePassword(User_PasswordReset $reset)
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
