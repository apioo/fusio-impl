<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Console\Marketplace;

use Fusio\Impl\Service;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ListCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ListCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\Marketplace\Repository\Remote
     */
    protected $remoteRepository;

    /**
     * @param \Fusio\Impl\Service\Marketplace\Repository\Remote $remoteRepository
     */
    public function __construct(Service\Marketplace\Repository\Remote $remoteRepository)
    {
        parent::__construct();

        $this->remoteRepository = $remoteRepository;
    }

    protected function configure()
    {
        $this
            ->setName('marketplace:list')
            ->setDescription('Lists all available apps on the marketplace')
            ->addOption('disable_ssl_verify', 'd', InputOption::VALUE_NONE, 'Disable SSL verification');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('disable_ssl_verify')) {
            $this->remoteRepository->setSslVerify(false);
        }

        $apps = $this->remoteRepository->fetchAll();

        $rows = [];
        foreach ($apps as $name => $app) {
            /** @var Service\Marketplace\App $app */
            $rows[] = [$name, $app->getVersion()];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Version'])
            ->setRows($rows);

        $table->render();

        return 0;
    }
}
