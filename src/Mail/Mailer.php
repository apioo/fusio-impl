<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Mail;

use Fusio\Impl\Service\Config;
use Psr\Log\LoggerInterface;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Mailer implements MailerInterface
{
    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    public function __construct(Config $config, LoggerInterface $logger, \Swift_Transport $transport)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->mailer = $transport;
    }

    public function send($subject, array $to, $body)
    {
        $message = \Swift_Message::newInstance();
        $message->setSubject($subject);

        $sender = $this->config->getValue('mail_sender');
        if (!empty($sender)) {
            $message->setFrom([$sender]);
        }

        $message->setTo($to);
        $message->setBody($body);

        $this->logger->info('Send registration mail', [
            'subject' => $subject,
            'body'    => $body,
        ]);

        $this->mailer->send($message);
    }
}
