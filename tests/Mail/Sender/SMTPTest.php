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
 * @license http://www.apache.org/licenses/LICENSE-2.0
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
