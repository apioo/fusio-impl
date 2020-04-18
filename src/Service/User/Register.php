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
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Mail\MailerInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;

/**
 * Register
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Register
{
    /**
     * @var \Fusio\Impl\Service\User
     */
    protected $userService;

    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Fusio\Impl\Service\User\Captcha
     */
    protected $captchaService;

    /**
     * @var \Fusio\Impl\Mail\MailerInterface
     */
    protected $mailer;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $psxConfig;

    /**
     * @param \Fusio\Impl\Service\User $userService
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Service\User\Captcha $captchaService
     * @param \Fusio\Impl\Mail\MailerInterface $mailer
     * @param \PSX\Framework\Config\Config $psxConfig
     */
    public function __construct(Service\User $userService, Service\Config $configService, Captcha $captchaService, MailerInterface $mailer, Config $psxConfig)
    {
        $this->userService    = $userService;
        $this->configService  = $configService;
        $this->captchaService = $captchaService;
        $this->mailer         = $mailer;
        $this->psxConfig      = $psxConfig;
    }

    public function register($name, $email, $password, $captcha)
    {
        $this->captchaService->assertCaptcha($captcha);

        // determine initial user status
        $status   = Table\User::STATUS_DISABLED;
        $approval = $this->configService->getValue('user_approval');
        if (!$approval) {
            $status = Table\User::STATUS_CONSUMER;
        }

        $scopes = $this->userService->getDefaultScopes();
        $userId = $this->userService->create(
            $status,
            $name,
            $email,
            $password,
            $scopes,
            UserContext::newAnonymousContext()
        );

        // send activation mail
        if ($approval) {
            $this->sendActivationMail($userId, $name, $email);
        }
    }

    protected function sendActivationMail($userId, $name, $email)
    {
        $payload = [
            'sub' => $userId,
            'exp' => time() + (60 * 60),
        ];

        $token   = JWT::encode($payload, $this->psxConfig->get('fusio_project_key'));
        $subject = $this->configService->getValue('mail_register_subject');
        $body    = $this->configService->getValue('mail_register_body');

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
