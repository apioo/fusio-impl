<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Console\App;

use Fusio\Impl\Backend\View;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ListCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ListCommand extends Command
{
    /**
     * @var \Fusio\Impl\Backend\View\App
     */
    protected $appView;

    /**
     * @param \Fusio\Impl\Backend\View\App $appView
     */
    public function __construct(View\App $appView)
    {
        parent::__construct();

        $this->appView = $appView;
    }

    protected function configure()
    {
        $this
            ->setName('app:list')
            ->setDescription('Lists available apps')
            ->addOption('startIndex', 'i', InputOption::VALUE_OPTIONAL, 'Start index of the list', 0)
            ->addArgument('search', InputArgument::OPTIONAL, 'Search value');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->appView->getCollection($input->getOption('startIndex'), $input->getArgument('search'));
        $rows   = [];

        foreach ($result->entry as $row) {
            $rows[] = [$row->id, $row->name];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name'])
            ->setRows($rows);

        $table->render();
    }
}
