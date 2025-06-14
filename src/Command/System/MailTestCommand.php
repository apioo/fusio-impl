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

namespace Fusio\Impl\Command\System;

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service\Mail\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MailTestCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class MailTestCommand extends Command
{
    use TypeSafeTrait;

    public function __construct(private Mailer $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:mail_test')
            ->setDescription('Sends a test mail to check whether the configured mailer works')
            ->addArgument('to', InputArgument::REQUIRED, 'The target mail address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = $this->getArgumentAsString($input, 'to');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Provided "to" email address has not a valid format');
        }

        $body = <<<TEXT

This is a Fusio test email, the mailer configuration of Fusio works as expected.
Thanks for choosing Fusio and happy testing.

More information about Fusio at:
https://www.fusio-project.org/

TEXT;

        $this->mailer->send('[Fusio] Test Mail', [$to], $body);

        return self::SUCCESS;
    }
}
