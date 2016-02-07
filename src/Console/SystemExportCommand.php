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
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SystemExportCommand
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class SystemExportCommand extends Command
{
    protected $exportService;

    public function __construct(Service\System\Export $exportService)
    {
        parent::__construct();

        $this->exportService = $exportService;
    }

    protected function configure()
    {
        $this
            ->setName('system:export')
            ->setDescription('Output all system data to a JSON structure')
            ->addArgument('file', InputArgument::OPTIONAL, 'Path of the JSON export file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');
        if (!empty($file)) {
            if (is_file($file)) {
                throw new RuntimeException('File already exists');
            }

            $bytes = file_put_contents($file, $this->exportService->export());

            $output->writeln('Export successful (' . $bytes . ' bytes written)');
        } else {
            $output->writeln($this->exportService->export());
        }
    }
}
