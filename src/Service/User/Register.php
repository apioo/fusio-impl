<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Http;
use PSX\Http\Exception as StatusCode;
use PSX\Json\Parser;

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
     * @var \PSX\Http\ClientInterface
     */
    protected $httpClient;

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
     * @param \PSX\Http\ClientInterface $httpClient
     * @param \Fusio\Impl\Mail\MailerInterface $mailer
     * @param \PSX\Framework\Config\Config $psxConfig
     */
    public function __construct(Service\User $userService, Service\Config $configService, Http\ClientInterface $httpClient, MailerInterface $mailer, Config $psxConfig)
    {
        $this->userService   = $userService;
        $this->configService = $configService;
        $this->httpClient    = $httpClient;
        $this->mailer        = $mailer;
        $this->psxConfig     = $psxConfig;
    }

    public function register($name, $email, $password, $captcha)
    {
        // verify captcha if secret is available
        $secret = $this->configService->getValue('recaptcha_secret');
        if (!empty($secret)) {
            $this->verifyCaptcha($captcha, $secret);
        }

        // determine initial user status
        $status   = Table\User::STATUS_DISABLED;
        $approval = $this->configService->getValue('user_approval');
        if (!$approval) {
            $status = Table\User::STATUS_CONSUMER;
        }

        $scopes = $this->getDefaultScopes();
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

    protected function verifyCaptcha($captcha, $secret)
    {
        $request = new Http\PostRequest('https://www.google.com/recaptcha/api/siteverify', [], [
            'secret'   => $secret,
            'response' => $captcha,
            'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
        ]);

        $response = $this->httpClient->request($request);

        if ($response->getStatusCode() == 200) {
            $data = Parser::decode($response->getBody());
            if ($data->success === true) {
                return true;
            }
        }

        throw new StatusCode\BadRequestException('Invalid captcha');
    }

    protected function getDefaultScopes()
    {
        $scopes = $this->configService->getValue('scopes_default');

        return array_filter(array_map('trim', explode(',', $scopes)), function ($scope) {
            // we filter out the backend scope since this would be a major
            // security issue
            return !empty($scope) && $scope != 'backend';
        });
    }
}
