<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Mail\TransportFactory;
use PSX\Framework\Config\Config;

/**
 * TransportFactoryTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
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
