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

use Fusio\Impl\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ImportSchemaCommand
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ImportSchemaCommand extends Command
{
    protected $schemaService;

    public function __construct(Service\Schema $schemaService)
    {
        parent::__construct();

        $this->schemaService = $schemaService;
    }

    protected function configure()
    {
        $this
            ->setName('import:jsonschema')
            ->setDescription('Imports a jsonschema into the system')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the json schema')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the json schema file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $file = $input->getArgument('file');

        if (!is_file($file)) {
            $output->writeln('Invalid schema file');
            return 1;
        }

        $this->schemaService->create($name, file_get_contents($file));

        $output->writeln('Import successful!');
    }
}
