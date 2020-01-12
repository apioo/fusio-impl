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

use Firebase\JWT\JWT;
use Fusio\Engine\User\ProviderInterface;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Mail\MailerInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
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
     * @var \Fusio\Impl\Table\User
     */
    protected $userTable;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Fusio\Impl\Mail\MailerInterface
     */
    protected $mailer;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $psxConfig;

    /**
     * @param \Fusio\Impl\Table\User $userTable
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Mail\MailerInterface $mailer
     * @param \PSX\Framework\Config\Config $psxConfig
     */
    public function __construct(Table\User $userTable, Service\Config $configService, MailerInterface $mailer, Config $psxConfig)
    {
        $this->userTable     = $userTable;
        $this->configService = $configService;
        $this->mailer        = $mailer;
        $this->psxConfig     = $psxConfig;
    }

    public function resetPassword(string $email)
    {
        $user = $this->userTable->getOneByEmail($email);

        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($user['provider'] != ProviderInterface::PROVIDER_SYSTEM) {
            throw new StatusCode\BadRequestException('Provided user is not a local user');
        }

        // set onetime token for the user
        $token = TokenGenerator::generateCode();

        $this->userTable->update([
            'id'    => $user['id'],
            'token' => $token
        ]);

        // send reset mail
        $this->sendResetMail($user['id'], $user['name'], $user['email'], $token);
    }

    public function changePassword(string $token, string $newPassword)
    {
        try {
            $payload = JWT::decode($token, $this->psxConfig->get('fusio_project_key'), ['HS256']);
        } catch (\UnexpectedValueException $e) {
            throw new StatusCode\BadRequestException('Invalid token provided');
        }

        $userId = $payload->sub ?? null;
        $user   = $this->userTable->get($userId);

        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if (empty($user['token'])) {
            throw new StatusCode\NotFoundException('No reset token available');
        }

        if ($user['token'] !== $payload->jti) {
            throw new StatusCode\NotFoundException('Invalid token provided');
        }

        $result = $this->userTable->changePassword($user['id'], null, $newPassword, false);

        if (!$result) {
            throw new StatusCode\BadRequestException('Could not change password');
        }

        // reset token
        $this->userTable->update([
            'id'    => $user['id'],
            'token' => ''
        ]);
    }

    protected function sendResetMail($userId, $name, $email, $token)
    {
        $payload = [
            'sub' => $userId,
            'exp' => time() + (60 * 60),
            'jti' => $token,
        ];

        $token   = JWT::encode($payload, $this->psxConfig->get('fusio_project_key'), 'HS256');
        $subject = $this->configService->getValue('mail_pw_reset_subject');
        $body    = $this->configService->getValue('mail_pw_reset_body');

        $values = array(
            'name'  => $name,
            'email' => $email,
            'token' => $token,
        );

        foreach ($values as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        $this->mailer->send($subject, [$email], $body);
    }
}
