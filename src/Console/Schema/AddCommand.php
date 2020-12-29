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

namespace Fusio\Impl\Console\Schema;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Schema_Create;
use Fusio\Impl\Backend\Model\Schema_Source;
use Fusio\Impl\Service;
use PSX\Json\Parser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * AddCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class AddCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\Schema
     */
    protected $schemaService;

    /**
     * @param \Fusio\Impl\Service\Schema $schemaService
     */
    public function __construct(Service\Schema $schemaService)
    {
        parent::__construct();

        $this->schemaService = $schemaService;
    }

    protected function configure()
    {
        $this
            ->setName('schema:add')
            ->setDescription('Imports a jsonschema into the system')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the json schema')
            ->addArgument('file', InputArgument::REQUIRED, 'Path to the json schema file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $input->getArgument('file');

        if (!is_file($file)) {
            $output->writeln('Invalid schema file');
            return 1;
        }

        $create = new Schema_Create();
        $create->setName($input->getArgument('name'));
        $create->setSource($this->parseSource($input->getArgument('file')));

        $this->schemaService->create(1, $create, UserContext::newAnonymousContext());

        $output->writeln('');
        $output->writeln('Schema successful created');
        $output->writeln('');

        return 0;
    }

    private function parseSource(string $file): Schema_Source
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        if (in_array($extension, ['yaml', 'yml'])) {
            $data = Yaml::parse(file_get_contents($file));
        } else {
            $data = Parser::decode(file_get_contents($file));
        }

        $source = new Schema_Source();
        foreach ($data as $key => $value) {
            $source->setProperty($key, $value);
        }

        return $source;
    }
}
