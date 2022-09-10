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

use PSX\Framework\Config\Config;
use PSX\Schema\Generator\Code\Chunks;
use PSX\Schema\Generator\FileAwareInterface;
use PSX\Schema\GeneratorFactory;
use PSX\Schema\SchemaManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModelCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ModelCommand extends Command
{
    private Config $config;
    private SchemaManagerInterface $schemaManager;

    public function __construct(Config $config, SchemaManagerInterface $schemaManager)
    {
        parent::__construct();

        $this->config = $config;
        $this->schemaManager = $schemaManager;
    }

    protected function configure()
    {
        $this
            ->setName('generate:model')
            ->setDescription('Generates model classes based on a TypeSchema specification');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srcFolder = $this->config->get('psx_path_src');
        if (!is_dir($srcFolder)) {
            throw new \RuntimeException('Configured src directory does not exist');
        }

        $source = $srcFolder . '/../resources/typeschema.json';
        $target = $srcFolder . '/Model';
        $format = 'php';
        $config = 'namespace=App\Model';

        if (!is_file($source)) {
            throw new \RuntimeException('TypeSchema file does not exist at resources/typeschema.json, please create the file in order to generate the models, more information about TypeSchema at: typeschema.org');
        }

        if (!is_dir($target)) {
            throw new \RuntimeException('The folder src/Model does not exist, please create it in order to generate the models');
        }

        $count = $this->generate($source, $target, $format, $config);

        $output->writeln('Generated ' . $count . ' files at ' . $target);

        return 1;
    }

    private function generate(string $source, string $target, string $format, string $config): int
    {
        $schema = $this->schemaManager->getSchema($source);

        $generator = (new GeneratorFactory())->getGenerator($format, $config);
        $response  = $generator->generate($schema);

        if ($generator instanceof FileAwareInterface && $response instanceof Chunks) {
            $count = 0;
            foreach ($response->getChunks() as $file => $code) {
                file_put_contents($target . '/' . $generator->getFileName($file), $generator->getFileContent($code));
                $count++;
            }

            return $count;
        } else {
            throw new \RuntimeException('The configured generator cant produce files');
        }
    }
}
