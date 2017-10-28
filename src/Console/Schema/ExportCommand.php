<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Doctrine\DBAL\Connection;
use PSX\Schema\Generator;
use PSX\Schema\SchemaInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ExportCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ExportCommand extends Command
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setName('schema:export')
            ->setDescription('Returns the complete json schema of a given schema name')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the json schema');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name   = $input->getArgument('name');
        $column = is_numeric($name) ? 'id' : 'name';

        $sql = $this->connection->createQueryBuilder()
            ->select('id', 'cache')
            ->from('fusio_schema')
            ->where($column . ' = :name')
            ->getSQL();

        $row = $this->connection->fetchAssoc($sql, ['name' => $name]);

        if (!empty($row)) {
            $generator = new Generator\JsonSchema();
            $schema    = unserialize($row['cache']);

            if ($schema instanceof SchemaInterface) {
                $output->writeln($generator->generate($schema));
            } else {
                $output->writeln('Invalid schema name');
                return 1;
            }
        } else {
            $output->writeln('Invalid schema name');
            return 1;
        }
    }
}
