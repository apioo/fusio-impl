<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * MigrationCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class MigrationCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\System\Deploy
     */
    protected $migrationTable;

    /**
     * @param \Fusio\Impl\Table\Deploy\Migration $migrationTable
     */
    public function __construct(Table\Deploy\Migration $migrationTable)
    {
        parent::__construct();

        $this->migrationTable = $migrationTable;
    }

    protected function configure()
    {
        $this
            ->setName('system:migration')
            ->setDescription('Shows all executed migration files');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->migrationTable->getAll();
        $rows   = [];

        foreach ($result as $row) {
            $rows[] = [$row->connection, $row->file, $row->executeDate->format('Y-m-d')];
        }

        $table = new Helper\Table($output);
        $table
            ->setHeaders(['Connection', 'File', 'Executed on'])
            ->setRows($rows);

        $table->render();
    }
}
