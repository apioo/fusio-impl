<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Service\Connection\Resolver;
use Psr\Log\LoggerInterface;
use PSX\Framework\Config\Config as FrameworkConfig;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Mailer implements MailerInterface
{
    /**
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @var \Fusio\Impl\Service\Connection\Resolver
     */
    protected $resolver;

    /**
     * @var \Fusio\Impl\Mail\SenderFactory
     */
    protected $senderFactory;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Fusio\Impl\Service\Config $configService
     * @param \Fusio\Impl\Service\Connection\Resolver $resolver
     * @param \Fusio\Impl\Mail\SenderFactory $senderFactory
     * @param \PSX\Framework\Config\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(Config $configService, Resolver $resolver, SenderFactory $senderFactory, FrameworkConfig $config, LoggerInterface $logger)
    {
        $this->configService = $configService;
        $this->resolver      = $resolver;
        $this->senderFactory = $senderFactory;
        $this->config        = $config;
        $this->logger        = $logger;
    }

    /**
     * @inheritdoc
     */
    public function send($subject, array $to, $body)
    {
        $dispatcher = $this->resolver->get(Resolver::TYPE_MAILER);
        if (!$dispatcher) {
            $dispatcher = new \Swift_Mailer($this->createTransport());
        }

        $from = $this->configService->getValue('mail_sender');
        if (empty($from)) {
            $from = 'registration@' . $this->getHostname();
        }

        $sender = $this->senderFactory->factory($dispatcher);
        if (!$sender instanceof SenderInterface) {
            throw new \RuntimeException('Could not find sender for dispatcher');
        }

        $sender->send($dispatcher, new Message(
            $from,
            $to,
            $subject,
            $body
        ));

        $this->logger->info('Send registration mail', [
            'to'      => implode(', ', $to),
            'subject' => $subject,
            'body'    => $body,
        ]);
    }

    /**
     * Tries to determine the current hostname
     *
     * @return string
     */
    private function getHostname()
    {
        $host = parse_url($this->config->get('psx_url'), PHP_URL_HOST);
        if (empty($host)) {
            $host = $_SERVER['SERVER_NAME'];
        }

        return $host;
    }

    /**
     * @return \Swift_Transport
     */
    private function createTransport()
    {
        if ($this->config['psx_debug'] === false) {
            $mailer = $this->config['fusio_mailer'];
            if (!empty($mailer)) {
                if ($mailer['transport'] == 'smtp') {
                    $transport = new \Swift_SmtpTransport($mailer['host'], $mailer['port']);
                    if (isset($mailer['encryption'])) {
                        $transport->setEncryption($mailer['encryption']);
                    }
                    if (isset($mailer['username'])) {
                        $transport->setUsername($mailer['username']);
                        $transport->setPassword($mailer['password']);
                    }
                    return $transport;
                }
            }

            return new \Swift_SendmailTransport();
        } else {
            return new \Swift_NullTransport();
        }
    }
}
