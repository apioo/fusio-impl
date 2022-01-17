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

namespace Fusio\Impl\Tests\Mail\Sender;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\Sender\SMTP;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\NullTransport;

/**
 * SMTPTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SMTPTest extends TestCase
{
    public function testAccept()
    {
        $sender = new SMTP();

        $this->assertTrue($sender->accept(new Mailer(new NullTransport())));
        $this->assertFalse($sender->accept(new \stdClass()));
    }

    public function testSend()
    {
        $transport = new MemoryTransport();
        $dispatcher = new Mailer($transport);

        $message = new Message('foo@bar.com', ['bar@foo.com'], 'foo', 'bar');

        $sender = new SMTP();
        $sender->send($dispatcher, $message);

        $this->assertEquals(1, count($transport->getMessages()));
    }
}
