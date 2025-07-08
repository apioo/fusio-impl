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

use Fusio\Impl\Command\TypeSafeTrait;
use Fusio\Impl\Service;
use PSX\Http\Exception\BadRequestException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * UpgradeCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class UpgradeCommand extends Command
{
    use TypeSafeTrait;

    public function __construct(
        private Service\Marketplace\Installer $installer,
        private Service\Marketplace\Factory $factory,
        private Service\System\ContextFactory $contextFactory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:update')
            ->setDescription('Updates an existing locally installed app')
            ->addArgument('type', InputArgument::REQUIRED, 'The type i.e. action or app')
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the app')
            ->addOption('disable_ssl_verify', 'd', InputOption::VALUE_NONE, 'Disable SSL verification');

        $this->contextFactory->addContextOptions($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawType = $this->getArgumentAsString($input, 'type');
        $name = $this->getOptionalArgumentAsString($input, 'name');

        $type = Service\Marketplace\Type::tryFrom($rawType);
        if ($type === null) {
            $type = Service\Marketplace\Type::APP;
            $name = 'fusio/' . $rawType;
        }

        try {
            $parts = explode('/', $name ?? '');
            $user = $parts[0] ?? throw new \RuntimeException('Provided an invalid name must be in the format [user]/[name]');
            $name = $parts[1] ?? throw new \RuntimeException('Provided an invalid name must be in the format [user]/[name]');

            $object = $this->installer->upgrade($type, $user, $name, $this->contextFactory->newCommandContext($input));

            $output->writeln('');
            $output->writeln('Updated ' . $type->value . ' ' . $object->getAuthor()?->getName() . '/' . $object->getName());
            $output->writeln('');
        } catch (BadRequestException $e) {
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
