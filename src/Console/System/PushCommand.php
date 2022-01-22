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

namespace Fusio\Impl\Console\System;

use Fusio\Engine\Push\ProviderInterface;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Provider\ProviderLoader;
use PSX\Dependency\AutowireResolverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * PushCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PushCommand extends Command
{
    private ProviderLoader $providerLoader;
    private AutowireResolverInterface $autowireResolver;

    public function __construct(ProviderLoader $providerLoader, AutowireResolverInterface $autowireResolver)
    {
        parent::__construct();

        $this->providerLoader = $providerLoader;
        $this->autowireResolver = $autowireResolver;
    }

    protected function configure()
    {
        $this
            ->setName('system:push')
            ->setAliases(['push'])
            ->setDescription('Pushes this Fusio instance to a cloud provider')
            ->addArgument('provider', InputArgument::REQUIRED, 'Name of the provider')
            ->addArgument('baseDir', InputArgument::OPTIONAL, 'Path to the Fusio directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $provider = $input->getArgument('provider');
        $baseDir  = $input->getArgument('baseDir') ?: getcwd();

        if (!is_dir($baseDir)) {
            throw new \RuntimeException('Invalid base dir');
        }

        if (!is_file($baseDir . '/configuration.php')) {
            throw new \RuntimeException('Looks like base dir is not a valid Fusio folder');
        }

        try {
            $output->writeln('Pushing ...');

            $provider = $this->getProviderFactory()->factory($provider);
            if (!$provider instanceof ProviderInterface) {
                throw new \RuntimeException('Invalid provider');
            }

            $generator = $provider->push($baseDir);
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

    private function getProviderFactory()
    {
        return new ProviderFactory(
            $this->providerLoader,
            $this->autowireResolver,
            'push',
            ProviderInterface::class
        );
    }
}
