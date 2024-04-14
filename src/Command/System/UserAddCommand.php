<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Command\System;

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\UserCreate;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * AddCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UserAddCommand extends Command
{
    private Service\User $userService;
    private Service\Config $configService;
    private Service\User\Validator $validator;
    private Service\System\ContextFactory $contextFactory;
    private Table\Role $roleTable;

    public function __construct(Service\User $userService, Service\Config $configService, Service\User\Validator $validator, Service\System\ContextFactory $contextFactory, Table\Role $roleTable)
    {
        $this->userService = $userService;
        $this->configService = $configService;
        $this->validator = $validator;
        $this->contextFactory = $contextFactory;
        $this->roleTable = $roleTable;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:user_add')
            ->setAliases(['adduser'])
            ->setDescription('Adds a new user account')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Role of the account [1=Administrator, 2=Backend, 3=Consumer]')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'The username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'The email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The password');

        $this->contextFactory->addContextOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = $this->contextFactory->newCommandContext($input);

        /** @var QuestionHelper $helper */
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
            $question->setValidator(function ($value) use ($context) {
                $this->validator->assertName($value, $context->getTenantId());
                return $value;
            });

            $name = $helper->ask($input, $output, $question);
        } else {
            if (!is_string($name)) {
                throw new RuntimeException('Provided an invalid name');
            }

            $this->validator->assertName($name, $context->getTenantId());
        }

        // email
        $email = $input->getOption('email');
        if ($email === null) {
            $question = new Question('Enter the email: ');
            $question->setValidator(function ($value) use ($context) {
                $this->validator->assertEmail($value, $context->getTenantId());
                return $value;
            });

            $email = $helper->ask($input, $output, $question);
        } else {
            if (!is_string($email)) {
                throw new RuntimeException('Provided an invalid email');
            }

            $this->validator->assertEmail($email, $context->getTenantId());
        }

        // password
        $password = $input->getOption('password');
        if ($password === null) {
            $question = new Question('Enter the password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                $this->validator->assertPassword($value, $this->configService->getValue('user_pw_length'));
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

            $this->validator->assertPassword($password, $this->configService->getValue('user_pw_length'));
        }

        // resolve role for tenant
        $roleName = match ($role) {
            1 => 'Administrator',
            2 => 'Backend',
            3 => 'Consumer',
            default => throw new RuntimeException('Provided an invalid role'),
        };

        $existing = $this->roleTable->findOneByTenantAndName($context->getTenantId(), $roleName);
        if (!$existing instanceof Table\Generated\RoleRow) {
            throw new RuntimeException('Could not resolve role');
        }

        // create user
        $create = new UserCreate();
        $create->setRoleId($existing->getId());
        $create->setStatus(Table\User::STATUS_ACTIVE);
        $create->setName($name);
        $create->setEmail($email);
        $create->setPassword($password);

        $this->userService->create($create, $context);

        $output->writeln('Created user ' . $name . ' successful');

        return self::SUCCESS;
    }
}
