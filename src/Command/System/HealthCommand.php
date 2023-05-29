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

use Fusio\Impl\Service\System\Health;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HealthCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class HealthCommand extends Command
{
    private Health $health;

    public function __construct(Health $health)
    {
        parent::__construct();

        $this->health = $health;
    }

    protected function configure()
    {
        $this
            ->setName('system:health')
            ->setDescription('Checks whether the system is healthy');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $result = $this->health->check();
        foreach ($result->getChecks() as $name => $check) {
            $healthy = $check['healthy'] ? '✓' : '✖';

            $output->writeln('[' . $healthy . '] ' . $name);

            if (isset($check['error'])) {
                $output->writeln('    ' . $check['error']);
            }
        }

        return $result->isHealthy() ? 0 : 1;
    }
}
