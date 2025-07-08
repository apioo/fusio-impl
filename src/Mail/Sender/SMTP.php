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

namespace Fusio\Impl\Mail\Sender;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\SenderInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * SMTP
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class SMTP implements SenderInterface
{
    public function accept(object $dispatcher): bool
    {
        return $dispatcher instanceof MailerInterface;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function send(object $dispatcher, Message $message): void
    {
        if (!$dispatcher instanceof MailerInterface) {
            throw new \InvalidArgumentException('Provided an invalid dispatcher');
        }

        $msg = new Email();
        $msg->from($message->getFrom());
        $msg->to(...$message->getTo());
        $msg->subject($message->getSubject());
        $msg->html($message->getBody());

        $dispatcher->send($msg);
    }
}
