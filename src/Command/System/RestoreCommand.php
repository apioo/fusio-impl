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

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service\System\Restorer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RestoreCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class RestoreCommand extends Command
{
    use TypeSafeTrait;

    private Restorer $restorer;

    public function __construct(Restorer $restorer)
    {
        $this->restorer = $restorer;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('system:restore')
            ->setDescription('Restores a deleted database record')
            ->addArgument('type', InputArgument::REQUIRED, 'Type must be one of: ' . implode(', ', $this->restorer->getTypes()))
            ->addArgument('id', InputArgument::REQUIRED, 'Name or id of the record');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $type = $this->getArgumentAsString($input, 'type');
        $id   = $this->getArgumentAsString($input, 'id');

        $result = $this->restorer->restore($type, $id);

        if ($result > 0) {
            $output->writeln('Restored ' . $result . ' record' . ($result > 1 ? 's' : ''));
            return self::SUCCESS;
        } else {
            $output->writeln('Restored no record');
            return self::FAILURE;
        }
    }
}
