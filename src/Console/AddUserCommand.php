<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Console;

use Fusio\Impl\Service\User as ServiceUser;
use Fusio\Impl\Service\User\ValidatorTrait;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * AddUserCommand
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AddUserCommand extends Command
{
    use ValidatorTrait;

    protected $userService;

    public function __construct(ServiceUser $userService)
    {
        parent::__construct();

        $this->userService = $userService;
    }

    protected function configure()
    {
        $this
            ->setName('adduser')
            ->setDescription('Adds a new user account');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // status
        $question = new Question('Choose the status for the account [0=Consumer, 1=Administrator]: ');
        $question->setValidator(function ($value) {
            if (preg_match('/^0|1$/', $value)) {
                return (int) $value;
            } else {
                throw new \Exception('Status must be either 0 or 1');
            }
        });

        $status = $helper->ask($input, $output, $question);

        // username
        $question = new Question('Enter the username: ');
        $question->setValidator(function ($value) {
            $this->assertName($value);
            return $value;
        });

        $name = $helper->ask($input, $output, $question);

        // email
        $question = new Question('Enter the email: ');
        $question->setValidator(function ($value) {
            $this->assertEmail($value);
            return $value;
        });

        $email = $helper->ask($input, $output, $question);

        // password
        $question = new Question('Enter the password: ');
        $question->setHidden(true);
        $question->setValidator(function ($value) {
            $this->assertPassword($value);
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

        // scopes
        if ($status === 0) {
            $scopes = ['consumer', 'authorization'];
        } elseif ($status === 1) {
            $scopes = ['backend', 'consumer', 'authorization'];
        } else {
            $scopes = [];
        }

        // password
        $this->userService->create(
            $status,
            $name,
            $email,
            $password,
            $scopes
        );

        $output->writeln('Created user ' . $name . ' successful');
    }
}
