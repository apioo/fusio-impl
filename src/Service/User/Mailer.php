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

use Fusio\Impl\Service\Mail\MailerInterface;
use Fusio\Impl\Service;
use PSX\Framework\Config\ConfigInterface;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Mailer
{
    private Service\Config $configService;
    private MailerInterface $mailer;
    private ConfigInterface $config;

    public function __construct(Service\Config $configService, MailerInterface $mailer, ConfigInterface $config)
    {
        $this->configService = $configService;
        $this->mailer = $mailer;
        $this->config = $config;
    }

    public function sendActivationMail(string $name, string $email, string $token)
    {
        $this->sendMail('mail_register', $email, [
            'apps_url' => $this->config->get('fusio_apps_url'),
            'name' => $name,
            'email' => $email,
            'token' => $token
        ]);
    }

    public function sendResetPasswordMail(string $name, string $email, string $token)
    {
        $this->sendMail('mail_pw_reset', $email, [
            'apps_url' => $this->config->get('fusio_apps_url'),
            'name' => $name,
            'email' => $email,
            'token' => $token
        ]);
    }

    public function sendPointsThresholdMail(string $name, string $email, int $points)
    {
        $this->sendMail('mail_points', $email, [
            'apps_url' => $this->config->get('fusio_apps_url'),
            'name' => $name,
            'email' => $email,
            'points' => $points
        ]);
    }

    private function sendMail(string $template, string $email, array $parameters)
    {
        $subject = $this->configService->getValue($template . '_subject');
        $body    = $this->configService->getValue($template . '_body');

        foreach ($parameters as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        $this->mailer->send($subject, [$email], $body);
    }
}
