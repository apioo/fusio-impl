<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Tests\Mail\Sender;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\Sender\SMTP;
use PHPUnit\Framework\TestCase;

/**
 * SMTPTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class SMTPTest extends TestCase
{
    public function testAccept()
    {
        $sender = new SMTP();

        $this->assertTrue($sender->accept(new \Swift_Mailer(new \Swift_NullTransport())));
        $this->assertFalse($sender->accept(new \stdClass()));
    }

    public function testSend()
    {
        $dispatcher = $this->getMockBuilder(\Swift_Mailer::class)
            ->setConstructorArgs([new \Swift_NullTransport()])
            ->setMethods(['send'])
            ->getMock();

        $dispatcher->expects($this->once())
            ->method('send')
            ->with($this->callback(function($message){
                /** @var \Swift_Mime_SimpleMessage $message */
                $this->assertInstanceOf(\Swift_Mime_SimpleMessage::class, $message);

                $this->assertEquals(['foo@bar.com' => null], $message->getFrom());
                $this->assertEquals(['bar@foo.com' => null], $message->getTo());
                $this->assertEquals('foo', $message->getSubject());
                $this->assertEquals('bar', $message->getBody());

                return true;
            }));

        $message = new Message('foo@bar.com', ['bar@foo.com'], 'foo', 'bar');

        $sender = new SMTP();
        $sender->send($dispatcher, $message);
    }
}
