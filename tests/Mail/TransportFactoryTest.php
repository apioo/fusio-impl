<?php
/*
 * PSX is a open source PHP framework to develop RESTful APIs.
 * For the current version and informations visit <http://phpsx.org>
 *
 * Copyright 2010-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

use Fusio\Impl\Mail\TransportFactory;
use PSX\Framework\Config\Config;

/**
 * TransportFactoryTest
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class TransportFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider configDataProvider
     */
    public function testCreateTransport($expectClass, Config $config)
    {
        $this->assertInstanceOf($expectClass, TransportFactory::createTransport($config));
    }

    public function configDataProvider()
    {
        return [
            [\Swift_NullTransport::class, new Config(['psx_debug' => true])],
            [\Swift_MailTransport::class, new Config(['psx_debug' => false])],
            [\Swift_SmtpTransport::class, new Config(['psx_debug' => false, 'fusio_mailer' => ['transport' => 'smtp', 'host' => '', 'port' => '']])],
            [\Swift_SmtpTransport::class, new Config(['psx_debug' => false, 'fusio_mailer' => ['transport' => 'smtp', 'host' => '', 'port' => '', 'encryption' => '']])],
            [\Swift_SmtpTransport::class, new Config(['psx_debug' => false, 'fusio_mailer' => ['transport' => 'smtp', 'host' => '', 'port' => '', 'encryption' => '', 'username' => '', 'password' => '']])],
        ];
    }

    public function testCreateTransportSmtp()
    {
        $config = new Config([
            'psx_debug'    => false,
            'fusio_mailer' => [
                'transport'  => 'smtp',
                'host'       => 'email-smtp.us-east-1.amazonaws.com',
                'port'       => 587,
                'encryption' => 'tls',
                'username'   => 'my-username',
                'password'   => 'my-password'
            ]
        ]);

        /** @var \Swift_SmtpTransport $transport */
        $transport = TransportFactory::createTransport($config);

        $this->assertInstanceOf(\Swift_SmtpTransport::class, $transport);
        $this->assertEquals('email-smtp.us-east-1.amazonaws.com', $transport->getHost());
        $this->assertEquals(587, $transport->getPort());
        $this->assertEquals('tls', $transport->getEncryption());
        $this->assertEquals('my-username', $transport->getUsername());
        $this->assertEquals('my-password', $transport->getPassword());
    }
}
