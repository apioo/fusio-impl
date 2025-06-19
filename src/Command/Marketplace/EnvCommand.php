<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Exception\MarketplaceException;
use Fusio\Impl\Service;
use Fusio\Impl\Service\Marketplace\InstallerInterface;
use Fusio\Impl\Service\Marketplace\RepositoryInterface;
use Fusio\Marketplace\MarketplaceApp;
use Fusio\Marketplace\MarketplaceMessageException;
use Sdkgen\Client\Exception\ClientException;
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

    private RepositoryInterface $repository;
    private InstallerInterface $installer;

    public function __construct(
        private Service\Marketplace\Factory $factory,
        private Service\System\ContextFactory $contextFactory,
        private Service\System\FrameworkConfig $frameworkConfig
    ) {
        parent::__construct();

        $this->repository = $this->factory->factory(Service\Marketplace\Type::APP)->getRepository();
        $this->installer = $this->factory->factory(Service\Marketplace\Type::APP)->getInstaller();
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:env')
            ->setDescription('Replaces env variables')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the app or "-" for all available apps');

        $this->contextFactory->addContextOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $this->getArgumentAsString($input, 'name');
        $context = $this->contextFactory->newCommandContext($input);

        try {
            if ($name === '-') {
                $apps = scandir($this->frameworkConfig->getAppsDir());
                if ($apps === false) {
                    throw new MarketplaceException('Could not scan apps directory');
                }

                foreach ($apps as $appName) {
                    if (isset($appName[0]) && $appName[0] === '.') {
                        continue;
                    }

                    $dir = $this->frameworkConfig->getAppsDir() . '/' . $appName;
                    if (!is_dir($dir)) {
                        continue;
                    }

                    $parts = explode('-', $appName, 2);

                    $this->replaceEnv($context, $output, ...$parts);
                }
            } else {
                $parts = explode('/', $name, 2);

                $this->replaceEnv($context, $output, ...$parts);
            }
        } catch (MarketplaceMessageException $e) {
            $output->writeln('');
            $output->writeln($e->getPayload()->getMessage() ?? 'Provided no message');
            if ($output->isVerbose()) {
                $output->writeln($e->getTraceAsString());
            }
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

    /**
     * @throws ClientException
     * @throws MarketplaceMessageException
     */
    private function replaceEnv(UserContext $context, OutputInterface $output, ?string $user = null, ?string $name = null): void
    {
        if (empty($name)) {
            $name = $user;
            $user = 'fusio';
        }

        if (empty($user) || empty($name)) {
            throw new MarketplaceException('Provided an invalid name');
        }

        $app = $this->repository->install($user, $name);
        if (!$app instanceof MarketplaceApp) {
            throw new MarketplaceException('Provided app does not exist');
        }

        if (!$this->installer instanceof Service\Marketplace\App\Installer) {
            throw new MarketplaceException('Provided an invalid installer');
        }

        $app = $this->installer->env($app, $context);

        $output->writeln('');
        $output->writeln('Replaced env ' . $app->getAuthor()?->getName() . '/' . $app->getName());
        $output->writeln('');
    }
}
