<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Command\Marketplace;

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service;
use Fusio\Marketplace\MarketplaceApp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * EnvCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class EnvCommand extends Command
{
    use TypeSafeTrait;

    private Service\Marketplace\Factory $factory;
    private Service\System\ContextFactory $contextFactory;

    public function __construct(Service\Marketplace\Factory $factory, Service\System\ContextFactory $contextFactory)
    {
        $this->factory = $factory;
        $this->contextFactory = $contextFactory;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:env')
            ->setDescription('Replaces env variables of an existing app')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app');

        $this->contextFactory->addContextOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $this->getArgumentAsString($input, 'name');

        try {
            $context = $this->contextFactory->newCommandContext($input);
            $repository = $this->factory->factory(Service\Marketplace\Type::APP)->getRepository();
            $installer = $this->factory->factory(Service\Marketplace\Type::APP)->getInstaller();

            $parts = explode('/', $name);
            $user = $parts[0] ?? null;
            $name = $parts[1] ?? null;

            if (empty($name)) {
                $name = $user;
                $user = 'fusio';
            }

            if (empty($user) || empty($name)) {
                throw new \RuntimeException('Provided an invalid name');
            }

            $app = $repository->install($user, $name);
            if (!$app instanceof MarketplaceApp) {
                throw new \RuntimeException('Provided app does not exist');
            }

            if (!$installer instanceof Service\Marketplace\App\Installer) {
                throw new \RuntimeException('Provided an invalid installer');
            }

            $app = $installer->env($app, $context);

            $output->writeln('');
            $output->writeln('Replaced env ' . $app->getAuthor()?->getName() . '/' . $app->getName());
            $output->writeln('');
        } catch (\Throwable $e) {
            $output->writeln('');
            $output->writeln($e->getMessage());
            if ($output->isVerbose()) {
                $output->writeln($e->getTraceAsString());
            }
            $output->writeln('');
        }

        return self::SUCCESS;
    }
}
