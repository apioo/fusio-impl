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

use Fusio\Impl\Service\System\Push;
use PSX\Framework\Config\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PushCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PushCommand extends Command
{
    /**
     * @var \Fusio\Impl\Service\System\Push
     */
    protected $pushService;

    /**
     * @var \PSX\Framework\Config\Config
     */
    protected $config;

    /**
     * @param \Fusio\Impl\Service\System\Push $pushService
     * @param \PSX\Framework\Config\Config $config
     */
    public function __construct(Push $pushService, Config $config)
    {
        parent::__construct();

        $this->pushService = $pushService;
        $this->config      = $config;
    }

    protected function configure()
    {
        $this
            ->setName('system:push')
            ->setAliases(['push'])
            ->setDescription('Pushes this Fusio instance to a cloud provider')
            ->addArgument('baseDir', InputArgument::OPTIONAL, 'Path to the Fusio directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseDir = $input->getArgument('baseDir') ?: getcwd();

        if (!is_dir($baseDir)) {
            throw new \RuntimeException('Invalid base dir');
        }

        try {
            $output->writeln('Pushing ...');

            $generator = $this->pushService->push($baseDir);

            foreach ($generator as $line) {
                $output->writeln($line);
            }

            $return = 0;
        } catch (\Throwable $e) {
            $output->writeln('An exception occurred during push.');
            $output->writeln('');
            $output->writeln('Message: ' . $e->getMessage());
            $output->writeln('Trace: ' . $e->getTraceAsString());

            $return = 1;
        }

        return $return;
    }
}
