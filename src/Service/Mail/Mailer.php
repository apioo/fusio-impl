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

namespace Fusio\Impl\Service\Mail;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\SenderInterface;
use Fusio\Impl\Service\Connection\Resolver;
use PSX\Framework\Config\ConfigInterface;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Mailer implements MailerInterface
{
    private Resolver $resolver;
    private SenderFactory $senderFactory;
    private ConfigInterface $config;
    private SymfonyMailerInterface $mailer;

    public function __construct(Resolver $resolver, SenderFactory $senderFactory, ConfigInterface $config, SymfonyMailerInterface $mailer)
    {
        $this->resolver = $resolver;
        $this->senderFactory = $senderFactory;
        $this->config = $config;
        $this->mailer = $mailer;
    }

    public function send(string $subject, array $to, string $body): void
    {
        $dispatcher = $this->resolver->get(Resolver::TYPE_MAILER);
        if (!$dispatcher) {
            $dispatcher = $this->mailer;
        }

        $from = $this->config->get('fusio_mail_sender');
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
    }

    /**
     * Tries to determine the current hostname
     */
    private function getHostname(): string
    {
        $host = parse_url($this->config->get('psx_url'), PHP_URL_HOST);
        if (empty($host)) {
            $host = $_SERVER['SERVER_NAME'] ?? 'unknown';
        }

        return $host;
    }

    private function createTransport(): TransportInterface
    {
        $mailer = $this->config->get('fusio_mailer');
        if ($this->config->get('psx_debug') === false && !empty($mailer)) {
            return Transport::fromDsn($mailer);
        } else {
            return new NullTransport();
        }
    }
}
