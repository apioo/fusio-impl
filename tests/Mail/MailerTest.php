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

namespace Fusio\Impl\Tests\Mail;

use Fusio\Impl\Service\Mail\MailerInterface;
use Fusio\Impl\Mail\Message;
use Fusio\Impl\Service\Mail\SenderFactory;
use Fusio\Impl\Mail\SenderInterface;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Mailer\Mailer;

/**
 * MailerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class MailerTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testMail()
    {
        $sender = $this->getMockBuilder(SenderInterface::class)
            ->setMethods(['accept', 'send'])
            ->getMock();

        $sender->expects($this->once())
            ->method('accept')
            ->with($this->callback(function($dispatcher){
                $this->assertInstanceOf(Mailer::class, $dispatcher);

                return true;
            }))
            ->willReturn(true);

        $sender->expects($this->once())
            ->method('send')
            ->with($this->callback(function($dispatcher){
                $this->assertInstanceOf(Mailer::class, $dispatcher);

                return true;
            }), $this->callback(function($message){
                /** @var Message $message */
                $this->assertInstanceOf(Message::class, $message);

                $this->assertEquals('registration@127.0.0.1', $message->getFrom());
                $this->assertEquals(['foo@bar.com'], $message->getTo());
                $this->assertEquals('test', $message->getSubject());
                $this->assertEquals('test body', $message->getBody());

                return true;
            }));

        /** @var SenderFactory $factory */
        $factory = Environment::getService('mailer_sender_factory');
        $factory->add($sender, 64);

        /** @var MailerInterface $mailer */
        $mailer = Environment::getService('mailer');
        $mailer->send('test', ['foo@bar.com'], 'test body');
    }
}
