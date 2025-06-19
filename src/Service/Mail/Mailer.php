<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Service\Mail;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\SenderInterface;
use Fusio\Impl\Service\Connection\Resolver;
use Fusio\Impl\Service\System\FrameworkConfig;
use Symfony\Component\Mailer\MailerInterface as SymfonyMailerInterface;

/**
 * Mailer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Mailer implements MailerInterface
{
    private Resolver $resolver;
    private SenderFactory $senderFactory;
    private FrameworkConfig $frameworkConfig;
    private SymfonyMailerInterface $mailer;

    public function __construct(Resolver $resolver, SenderFactory $senderFactory, FrameworkConfig $frameworkConfig, SymfonyMailerInterface $mailer)
    {
        $this->resolver = $resolver;
        $this->senderFactory = $senderFactory;
        $this->frameworkConfig = $frameworkConfig;
        $this->mailer = $mailer;
    }

    public function send(string $subject, array $to, string $body): void
    {
        $dispatcher = $this->resolver->get(Resolver::TYPE_MAILER);
        if (!$dispatcher) {
            $dispatcher = $this->mailer;
        }

        $from = $this->frameworkConfig->getMailSender();
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
        $host = parse_url($this->frameworkConfig->getUrl(), PHP_URL_HOST);
        if (empty($host)) {
            $host = $_SERVER['SERVER_NAME'] ?? 'unknown';
        }

        return $host;
    }
}
