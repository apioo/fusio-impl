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

namespace Fusio\Impl\Console\Generate;

use Fusio\Engine\ConnectorInterface;
use PSX\Framework\Config\Config;
use PSX\Sql\Generator\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TableCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class TableCommand extends Command
{
    private Config $config;
    private ConnectorInterface $connector;

    public function __construct(Config $config, ConnectorInterface $connector)
    {
        parent::__construct();

        $this->config = $config;
        $this->connector = $connector;
    }

    protected function configure()
    {
        $this
            ->setName('generate:table')
            ->setDescription('Generates table and row classes for the configured connection')
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'Optional the connection which is used', 'System');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srcFolder = $this->config->get('psx_path_src');
        if (!is_dir($srcFolder)) {
            throw new \RuntimeException('Configured src directory does not exist');
        }

        $tableFolder = $srcFolder . '/Table';
        if (!is_dir($tableFolder)) {
            throw new \RuntimeException('The folder src/Table does not exist, please create it in order to generate the table classes');
        }

        $tableGeneratedFolder = $tableFolder . '/Generated';
        if (!is_dir($tableGeneratedFolder)) {
            throw new \RuntimeException('The folder src/Table/Generated does not exist, please create it in order to generate the table classes');
        }

        $connection = $this->connector->getConnection($input->getOption('connection'));

        $generator = new Generator($connection, 'App\\Table\\Generated', 'app_');
        $count = 0;
        foreach ($generator->generate() as $className => $source) {
            file_put_contents($tableGeneratedFolder . '/' . $className . '.php', '<?php' . "\n\n" . $source);

            if (str_ends_with($className, 'Table')) {
                $overwriteClass = substr($className, 0, -5);
                $overwriteFile = $tableFolder . '/' . $overwriteClass . '.php';
                if (!is_file($overwriteFile)) {
                    file_put_contents($overwriteFile, $this->getOverwriteClass($className, $overwriteClass));
                }
            }

            $count++;
        }

        $output->writeln('Generated ' . $count . ' files at ' . $tableGeneratedFolder);

        return 1;
    }

    private function getOverwriteClass(string $className, string $overwriteClass): string
    {
        return <<<PHP
<?php

namespace App\Table;

use App\\Table\\Generated\\{$className};

class {$overwriteClass} extends {$className}
{
}

PHP;

    }
}
