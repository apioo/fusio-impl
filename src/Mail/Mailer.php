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

use Fusio\Impl\Service;
use Fusio\Impl\Service\Connection\Resolver;
use Psr\Log\LoggerInterface;
use PSX\Framework\Config\Config;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Mailer implements MailerInterface
{
    private Service\Config $configService;
    private Resolver $resolver;
    private SenderFactory $senderFactory;
    private Config $config;
    private LoggerInterface $logger;

    public function __construct(Service\Config $configService, Resolver $resolver, SenderFactory $senderFactory, Config $config, LoggerInterface $logger)
    {
        $this->configService = $configService;
        $this->resolver      = $resolver;
        $this->senderFactory = $senderFactory;
        $this->config        = $config;
        $this->logger        = $logger;
    }

    public function send(string $subject, array $to, string $body): void
    {
        $dispatcher = $this->resolver->get(Resolver::TYPE_MAILER);
        if (!$dispatcher) {
            $dispatcher = new SymfonyMailer($this->createTransport());
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
     */
    private function getHostname(): string
    {
        $host = parse_url($this->config->get('psx_url'), PHP_URL_HOST);
        if (empty($host)) {
            $host = $_SERVER['SERVER_NAME'];
        }

        return $host;
    }

    private function createTransport(): TransportInterface
    {
        $mailer = $this->config['fusio_mailer'];
        if ($this->config['psx_debug'] === false && !empty($mailer)) {
            return Transport::fromDsn($mailer);
        } else {
            return new NullTransport();
        }
    }
}
