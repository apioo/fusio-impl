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

namespace Fusio\Impl\Command\System;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\UserCreate;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * AddCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class UserAddCommand extends Command
{
    private Service\User $userService;
    private Service\Config $configService;

    public function __construct(Service\User $userService, Service\Config $configService)
    {
        parent::__construct();

        $this->userService   = $userService;
        $this->configService = $configService;
    }

    protected function configure()
    {
        $this
            ->setName('system:user_add')
            ->setAliases(['adduser'])
            ->setDescription('Adds a new user account')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Role of the account [1=Administrator, 2=Backend, 3=Consumer]')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'The username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'The email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // role
        $role = $input->getOption('role');
        if ($role === null) {
            $question = new Question('Choose the role of the account [1=Administrator, 2=Backend, 3=Consumer]: ');
            $role = (int) $helper->ask($input, $output, $question);
        } elseif (is_string($role)) {
            $role = (int) $role;
        } else {
            throw new RuntimeException('Provided an invalid role');
        }

        // username
        $name = $input->getOption('username');
        if ($name === null) {
            $question = new Question('Enter the username: ');
            $question->setValidator(function ($value) {
                Service\User\Validator::assertName($value);
                return $value;
            });

            $name = $helper->ask($input, $output, $question);
        } else {
            if (!is_string($name)) {
                throw new RuntimeException('Provided an invalid name');
            }

            Service\User\Validator::assertName($name);
        }

        // email
        $email = $input->getOption('email');
        if ($email === null) {
            $question = new Question('Enter the email: ');
            $question->setValidator(function ($value) {
                Service\User\Validator::assertEmail($value);
                return $value;
            });

            $email = $helper->ask($input, $output, $question);
        } else {
            if (!is_string($email)) {
                throw new RuntimeException('Provided an invalid email');
            }

            Service\User\Validator::assertEmail($email);
        }

        // password
        $password = $input->getOption('password');
        if ($password === null) {
            $question = new Question('Enter the password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                Service\User\Validator::assertPassword($value, $this->configService->getValue('user_pw_length'));
                return $value;
            });

            $password = $helper->ask($input, $output, $question);

            // repeat password
            $question = new Question('Repeat the password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) use ($password) {
                if ($value != $password) {
                    throw new RuntimeException('The password does not match');
                } else {
                    return true;
                }
            });

            $helper->ask($input, $output, $question);
        } else {
            if (!is_string($password)) {
                throw new RuntimeException('Provided an invalid password');
            }

            Service\User\Validator::assertPassword($password, $this->configService->getValue('user_pw_length'));
        }

        // create user
        $create = new UserCreate();
        $create->setRoleId($role);
        $create->setStatus(Table\User::STATUS_ACTIVE);
        $create->setName($name);
        $create->setEmail($email);
        $create->setPassword($password);

        $this->userService->create($create, UserContext::newCommandContext());

        $output->writeln('Created user ' . $name . ' successful');

        return 0;
    }
}
