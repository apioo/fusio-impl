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

use PSX\Api\GeneratorFactory;
use PSX\Api\GeneratorFactoryInterface;
use PSX\Api\Listing\FilterFactoryInterface;
use PSX\Api\Listing\FilterInterface;
use PSX\Api\ListingInterface;
use PSX\Framework\Config\Config;
use PSX\Schema\Generator\Code\Chunks;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SdkCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class SdkCommand extends Command
{
    private Config $config;
    private ListingInterface $listing;
    private GeneratorFactoryInterface $factory;
    private ?FilterFactoryInterface $filterFactory;

    public function __construct(Config $config, ListingInterface $listing, GeneratorFactoryInterface $factory, FilterFactoryInterface $filterFactory)
    {
        parent::__construct();

        $this->config = $config;
        $this->listing = $listing;
        $this->factory = $factory;
        $this->filterFactory = $filterFactory;
    }

    protected function configure()
    {
        $this
            ->setName('generate:sdk')
            ->setDescription('Generates a client SDK')
            ->addArgument('format', InputArgument::OPTIONAL, 'The target format of the SDK', GeneratorFactoryInterface::CLIENT_TYPESCRIPT)
            ->addOption('connection', 'c', InputOption::VALUE_REQUIRED, 'The connection which is used', 'System')
            ->addOption('namespace', 's', InputOption::VALUE_REQUIRED, 'A namespace which is used', null)
            ->addOption('filter', 'e', InputOption::VALUE_REQUIRED, 'Optional a filter which is used', 'default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srcFolder = $this->config->get('psx_path_src');
        if (!is_dir($srcFolder)) {
            throw new \RuntimeException('Configured src directory does not exist');
        }

        $dir = $srcFolder . '/../output';
        if (!is_dir($dir)) {
            throw new \RuntimeException('The folder output/ does not exist, please create it in order to generate the SDK');
        }

        $format = $input->getArgument('format') ?? GeneratorFactoryInterface::CLIENT_TYPESCRIPT;
        if (!in_array($format, GeneratorFactory::getPossibleTypes())) {
            throw new \InvalidArgumentException('Provided an invalid format, possible values are: ' . implode(', ', GeneratorFactory::getPossibleTypes()));
        }

        $config = $this->getConfig($input);
        $filter = null;
        $filterName = $input->getOption('filter');
        if (!empty($filterName)) {
            $filter = $this->filterFactory->getFilter((string) $filterName);
            if ($filter === null) {
                throw new \RuntimeException('Provided an invalid filter name');
            }
        }

        $generator = $this->factory->getGenerator($format, $config);
        $extension = $this->factory->getFileExtension($format, $config);

        $output->writeln('Generating ...');

        $content = $generator->generate($this->listing->findAll(null, $filter));

        if ($content instanceof Chunks) {
            if (!empty($filterName)) {
                $file = 'sdk-' . $format .  '-' . $filterName . '.zip';
            } else {
                $file = 'sdk-' . $format .  '.zip';
            }

            $content->writeTo($dir . '/' . $file);
        } else {
            if (!empty($filterName)) {
                $file = 'output-' . $format . '-' . $filterName . '.' . $extension;
            } else {
                $file = 'output-' . $format . '.' . $extension;
            }

            file_put_contents($dir . '/' . $file, $content);
        }

        $output->writeln('Successful!');

        return 1;
    }

    private function getConfig(InputInterface $input): ?string
    {
        $namespace = $input->getOption('namespace');
        $options = [];
        if (!empty($namespace)) {
            $options['namespace'] = $namespace;
        }

        if (!empty($options)) {
            return http_build_query($options);
        } else {
            return null;
        }
    }
}
