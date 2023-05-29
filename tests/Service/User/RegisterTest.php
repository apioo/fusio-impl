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

namespace Fusio\Impl\Tests\Service\User;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Service\User\Register;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\Fixture;
use Fusio\Model\Backend\ConfigUpdate;
use Fusio\Model\Consumer\UserRegister;
use PSX\Framework\Test\ControllerDbTestCase;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\BadRequestException;
use PSX\Sql\TableManagerInterface;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RegisterTest extends ControllerDbTestCase
{
    public function getDataSet(): array
    {
        return Fixture::getDataSet();
    }

    public function testRegister()
    {
        $register = new Register(
            Environment::getService(Service\User::class),
            $this->newCaptchaService(true),
            Environment::getService(Service\User\Token::class),
            $this->newMailer(true),
            $this->newConfig('private_key', true),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Role::class)
        );

        $user = new UserRegister();
        $user->setName('new_user');
        $user->setEmail('user@host.com');
        $user->setPassword('test1234');
        $user->setCaptcha('result');
        $register->register($user);

        // check user
        /** @var Connection $connection */
        $connection = Environment::getService(Connection::class);

        $user = $connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

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
            Environment::getService(Service\User::class),
            $this->newCaptchaService(true),
            Environment::getService(Service\User\Token::class),
            $this->newMailer(false),
            $this->newConfig('private_key', false),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Role::class)
        );

        $user = new UserRegister();
        $user->setName('new_user');
        $user->setEmail('user@host.com');
        $user->setPassword('test1234');
        $user->setCaptcha('result');
        $register->register($user);

        // check user
        /** @var Connection $connection */
        $connection = Environment::getService(Connection::class);

        $user = $connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(Table\User::STATUS_ACTIVE, $user['status']);
        $this->assertEquals('', $user['remote_id']);
        $this->assertEquals('new_user', $user['name']);
        $this->assertEquals('user@host.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    public function testRegisterNoCaptchaSecret()
    {
        $register = new Register(
            Environment::getService(Service\User::class),
            $this->newCaptchaService(true),
            Environment::getService(Service\User\Token::class),
            $this->newMailer(true),
            $this->newConfig('', true),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Role::class)
        );

        $user = new UserRegister();
        $user->setName('new_user');
        $user->setEmail('user@host.com');
        $user->setPassword('test1234');
        $user->setCaptcha('result');
        $register->register($user);

        // check user
        /** @var Connection $connection */
        $connection = Environment::getService(Connection::class);

        $user = $connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => 6]);

        $this->assertEquals(6, $user['id']);
        $this->assertEquals(1, $user['provider']);
        $this->assertEquals(Table\User::STATUS_DISABLED, $user['status']);
        $this->assertEquals('', $user['remote_id']);
        $this->assertEquals('new_user', $user['name']);
        $this->assertEquals('user@host.com', $user['email']);
        $this->assertNotEmpty($user['password']);
    }

    public function testRegisterInvalidCaptcha()
    {
        $this->expectException(BadRequestException::class);

        $register = new Register(
            Environment::getService(Service\User::class),
            $this->newCaptchaService(false),
            Environment::getService(Service\User\Token::class),
            $this->newMailer(false),
            $this->newConfig('private_key', true),
            Environment::getService(TableManagerInterface::class)->getTable(Table\Role::class)
        );

        $user = new UserRegister();
        $user->setName('new_user');
        $user->setEmail('user@host.com');
        $user->setPassword('test1234');
        $user->setCaptcha('result');
        $register->register($user);
    }

    private function newCaptchaService(bool $success): Service\User\Captcha
    {
        $captcha = $this->getMockBuilder(Service\User\Captcha::class)
            ->disableOriginalConstructor()
            ->setMethods(['assertCaptcha'])
            ->getMock();

        if ($success) {
            $captcha->expects($this->once())
                ->method('assertCaptcha');
        } else {
            $captcha->expects($this->once())
                ->method('assertCaptcha')
                ->willThrowException(new BadRequestException('Invalid captcha'));
        }

        return $captcha;
    }

    private function newConfig(string $reCaptchaSecret, bool $userApproval): Service\Config
    {
        /** @var Service\Config $config */
        $config = Environment::getService(Service\Config::class);

        $update = new ConfigUpdate();
        $update->setValue($reCaptchaSecret);
        $config->update($this->getConfigId('recaptcha_secret'), $update, UserContext::newAnonymousContext());

        $update = new ConfigUpdate();
        $update->setValue($userApproval);
        $config->update($this->getConfigId('user_approval'), $update, UserContext::newAnonymousContext()); 

        return $config;
    }

    private function newMailer(bool $send): Service\User\Mailer
    {
        $mailer = $this->getMockBuilder(Service\User\Mailer::class)
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

    private function getConfigId(string $name): int
    {
        /** @var Connection $connection */
        $connection = Environment::getService(Connection::class);
        $configId   = $connection->fetchOne('SELECT id FROM fusio_config WHERE name = :name', ['name' => $name]);

        return $configId;
    }
}
