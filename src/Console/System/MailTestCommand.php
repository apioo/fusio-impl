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

namespace Fusio\Impl\Console\System;

use Fusio\Impl\Console\TypeSafeTrait;
use Fusio\Impl\Service\Mail\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MailTestCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class MailTestCommand extends Command
{
    use TypeSafeTrait;

    private Mailer $mailer;

    public function __construct(Mailer $mailer)
    {
        parent::__construct();

        $this->mailer = $mailer;
    }

    protected function configure()
    {
        $this
            ->setName('system:mail_test')
            ->setDescription('Sends a test mail to check whether the configured mailer works')
            ->addArgument('to', InputArgument::REQUIRED, 'The target mail address');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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

        return 0;
    }
}
