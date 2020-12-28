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

namespace Fusio\Impl\Console\User;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\User_Create;
use Fusio\Impl\Service\User as ServiceUser;
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
 * @link    http://fusio-project.org
 */
class AddCommand extends Command
{
    protected $userService;

    public function __construct(ServiceUser $userService)
    {
        parent::__construct();

        $this->userService = $userService;
    }

    protected function configure()
    {
        $this
            ->setName('user:add')
            ->setAliases(['adduser'])
            ->setDescription('Adds a new user account')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Status of the account [0=Consumer, 1=Administrator]')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'The username')
            ->addOption('email', 'e', InputOption::VALUE_OPTIONAL, 'The email')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'The password');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // status
        $status = $input->getOption('status');
        if ($status === null) {
            $question = new Question('Choose the status of the account [0=Consumer, 1=Administrator]: ');
            $question->setValidator(function ($value) {
                return ServiceUser\Validator::assertStatus($value);
            });

            $status = $helper->ask($input, $output, $question);
        } else {
            $status = ServiceUser\Validator::assertStatus($status);
        }

        // username
        $name = $input->getOption('username');
        if ($name === null) {
            $question = new Question('Enter the username: ');
            $question->setValidator(function ($value) {
                ServiceUser\Validator::assertName($value);
                return $value;
            });

            $name = $helper->ask($input, $output, $question);
        } else {
            ServiceUser\Validator::assertName($name);
        }

        // email
        $email = $input->getOption('email');
        if ($email === null) {
            $question = new Question('Enter the email: ');
            $question->setValidator(function ($value) {
                ServiceUser\Validator::assertEmail($value);
                return $value;
            });

            $email = $helper->ask($input, $output, $question);
        } else {
            ServiceUser\Validator::assertEmail($email);
        }

        // password
        $password = $input->getOption('password');
        if ($password === null) {
            $question = new Question('Enter the password: ');
            $question->setHidden(true);
            $question->setValidator(function ($value) {
                $this->userService->assertPasswordComplexity($value);
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
            $this->userService->assertPasswordComplexity($password);
        }

        // scopes
        if ($status === 0) {
            $scopes = $this->userService->getDefaultScopes();
        } elseif ($status === 1) {
            $scopes = ['backend', 'consumer', 'authorization'];
        } else {
            $scopes = [];
        }

        // create user
        $create = new User_Create();
        $create->setStatus($status);
        $create->setName($name);
        $create->setEmail($email);
        $create->setPassword($password);
        $create->setScopes($scopes);

        $this->userService->create($create, UserContext::newCommandContext());

        $output->writeln('Created user ' . $name . ' successful');

        return 0;
    }
}
