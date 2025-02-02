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

namespace Fusio\Impl\Tests\Service\User;

use Fusio\Impl\Service;
use Fusio\Impl\Service\System\ContextFactory;
use Fusio\Impl\Service\User\Register;
use Fusio\Impl\Table;
use Fusio\Impl\Tests\DbTestCase;
use Fusio\Model\Backend\ConfigUpdate;
use Fusio\Model\Consumer\UserRegister;
use PSX\Framework\Test\Environment;
use PSX\Http\Exception\BadRequestException;
use PSX\Sql\TableManagerInterface;

/**
 * RegisterTest
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class RegisterTest extends DbTestCase
{
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();
        $userId = $register->register($user, $context);

        // check user
        $user = $this->connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => $userId]);

        $this->assertEquals($userId, $user['id']);
        $this->assertEquals(null, $user['identity_id']);
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();
        $userId = $register->register($user, $context);

        // check user
        $user = $this->connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => $userId]);

        $this->assertEquals($userId, $user['id']);
        $this->assertEquals(null, $user['identity_id']);
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();
        $userId = $register->register($user, $context);

        // check user
        $user = $this->connection->fetchAssociative('SELECT * FROM fusio_user WHERE id = :id', ['id' => $userId]);

        $this->assertEquals($userId, $user['id']);
        $this->assertEquals(null, $user['identity_id']);
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();
        $register->register($user, $context);
    }

    private function newCaptchaService(bool $success): Service\User\Captcha
    {
        $captcha = $this->getMockBuilder(Service\User\Captcha::class)
            ->disableOriginalConstructor()
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
        $context = Environment::getService(ContextFactory::class)->newAnonymousContext();

        $update = new ConfigUpdate();
        $update->setValue($reCaptchaSecret);
        $config->update($this->getConfigId('recaptcha_secret'), $update, $context);

        $update = new ConfigUpdate();
        $update->setValue($userApproval);
        $config->update($this->getConfigId('user_approval'), $update, $context);

        return $config;
    }

    private function newMailer(bool $send): Service\User\Mailer
    {
        $mailer = $this->getMockBuilder(Service\User\Mailer::class)
            ->disableOriginalConstructor()
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
        return $this->connection->fetchOne('SELECT id FROM fusio_config WHERE name = :name', ['name' => $name]);
    }
}
