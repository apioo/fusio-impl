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

namespace Fusio\Impl\Tests\Service\User;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Config;
use Fusio\Impl\Service\User\Captcha;
use Fusio\Impl\Service\User\Mailer;
use Fusio\Impl\Service\User\Register;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\BadRequestException;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    http://phpsx.org
 */
class RegisterTest extends ControllerDbTestCase
{
    public function getDataSet()
    {
        return Fixture::getDataSet();
    }

    public function testRegister()
    {
        $register = new Register(
            Environment::getService('user_service'),
            $this->newCaptchaService(true),
            Environment::getService('user_token_service'),
            $this->newMailer(true),
            $this->newConfig('private_key', true)
        );

        $register->register('new_user', 'user@host.com', 'test1234', 'result');

        // check user
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Environment::getService('connection');

        $user = $connection->fetchAssoc('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(Table\User::STATUS_DISABLED, $user['status']);
        $this->assertEquals('', $user['remote_id']);
        $this->assertEquals('new_user', $user['name']);
        $this->assertEquals('user@host.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    public function testRegisterNoApproval()
    {
        $register = new Register(
            Environment::getService('user_service'),
            $this->newCaptchaService(true),
            Environment::getService('user_token_service'),
            $this->newMailer(false),
            $this->newConfig('private_key', false)
        );

        $register->register('new_user', 'user@host.com', 'test1234', 'result');

        // check user
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Environment::getService('connection');

        $user = $connection->fetchAssoc('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(Table\User::STATUS_CONSUMER, $user['status']);
        $this->assertEquals('', $user['remote_id']);
        $this->assertEquals('new_user', $user['name']);
        $this->assertEquals('user@host.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    public function testRegisterNoCaptchaSecret()
    {
        $register = new Register(
            Environment::getService('user_service'),
            $this->newCaptchaService(true),
            Environment::getService('user_token_service'),
            $this->newMailer(true),
            $this->newConfig('', true)
        );

        $register->register('new_user', 'user@host.com', 'test1234', 'result');

        // check user
        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = Environment::getService('connection');

        $user = $connection->fetchAssoc('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(Table\User::STATUS_DISABLED, $user['status']);
        $this->assertEquals('', $user['remote_id']);
        $this->assertEquals('new_user', $user['name']);
        $this->assertEquals('user@host.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    /**
     * @expectedException \PSX\Http\Exception\BadRequestException
     */
    public function testRegisterInvalidCaptcha()
    {
        $register = new Register(
            Environment::getService('user_service'),
            $this->newCaptchaService(false),
            Environment::getService('user_token_service'),
            $this->newMailer(false),
            $this->newConfig('private_key', true)
        );

        $register->register('new_user', 'user@host.com', 'test1234', 'result');
    }

    /**
     * @param boolean $success
     * @return Captcha
     */
    private function newCaptchaService($success)
    {
        $captcha = $this->getMockBuilder(Captcha::class)
            ->disableOriginalConstructor()
            ->setMethods(['assertCaptcha'])
            ->getMock();

        if ($success) {
            $captcha->expects($this->once())
                ->method('assertCaptcha')
                ->willReturn($this->returnValue(true));
        } else {
            $captcha->expects($this->once())
                ->method('assertCaptcha')
                ->willThrowException(new BadRequestException('Invalid captcha'));
        }

        return $captcha;
    }

    /**
     * @return \Fusio\Impl\Service\Config
     */
    private function newConfig($reCaptchaSecret, $userApproval)
    {
        /** @var Config $config */
        $config = Environment::getService('config_service');

        $config->update($this->getConfigId('recaptcha_secret'), $reCaptchaSecret, UserContext::newAnonymousContext()); 
        $config->update($this->getConfigId('user_approval'), $userApproval, UserContext::newAnonymousContext()); 

        return $config;
    }

    /**
     * @param boolean $send
     * @return \Fusio\Impl\Service\User\Mailer
     */
    private function newMailer($send)
    {
        $mailer = $this->getMockBuilder(Mailer::class)
            ->disableOriginalConstructor()
            ->setMethods(['sendActivationMail'])
            ->getMock();

        if ($send) {
            $mailer->expects($this->once())
                ->method('sendActivationMail');
        } else {
            $mailer->expects($this->never())
                ->method('sendActivationMail');
        }

        return $mailer;
    }

    /**
     * @param string $name
     * @return integer
     */
    private function getConfigId($name)
    {
        /** @var Connection $connection */
        $connection = Environment::getService('connection');
        $configId   = $connection->fetchColumn('SELECT id FROM fusio_config WHERE name = :name', ['name' => $name]);

        return $configId;
    }
}
