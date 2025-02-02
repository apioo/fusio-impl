<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
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

namespace Fusio\Impl\Tests\Mail;

use Fusio\Impl\Mail\Message;
use Fusio\Impl\Mail\SenderInterface;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Connection\Resolver;
use Fusio\Impl\Service\Mail\SenderFactory;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;

/**
 * MailerTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MailerTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testMail()
    {
        $sender = $this->getMockBuilder(SenderInterface::class)
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

        $mailer = new Service\Mail\Mailer(
            Environment::getService(Resolver::class),
            new SenderFactory([$sender]),
            Environment::getService(Service\System\FrameworkConfig::class),
            Environment::getService(MailerInterface::class)
        );

        $mailer->send('test', ['foo@bar.com'], 'test body');
    }
}
