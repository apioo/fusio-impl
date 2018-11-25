<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Mail\Sender;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\SenderInterface;

/**
 * SMTP
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SMTP implements SenderInterface
{
    /**
     * @inheritdoc
     */
    public function accept($dispatcher)
    {
        return $dispatcher instanceof \Swift_Transport;
    }

    /**
     * @param \Swift_Transport $dispatcher
     * @param \Fusio\Impl\Mail\Message $message
     * @return void
     */
    public function send($dispatcher, Message $message)
    {
        $msg = \Swift_Message::newInstance();
        $msg->setFrom([$message->getFrom()]);
        $msg->setTo($message->getTo());
        $msg->setSubject($message->getSubject());
        $msg->setBody($message->getBody());

        $dispatcher->send($msg);
    }
}
