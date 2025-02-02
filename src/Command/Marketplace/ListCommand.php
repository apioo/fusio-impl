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
use Fusio\Marketplace\MarketplaceActionCollection;
use Fusio\Marketplace\MarketplaceAppCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ListCommand
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class ListCommand extends Command
{
    use TypeSafeTrait;

    private Service\Marketplace\Factory $factory;

    public function __construct(Service\Marketplace\Factory $factory)
    {
        parent::__construct();

        $this->factory = $factory;
    }

    protected function configure(): void
    {
        $this
            ->setName('marketplace:list')
            ->addArgument('type', InputArgument::OPTIONAL, 'The type i.e. action or app')
            ->addArgument('query', InputArgument::OPTIONAL, 'To search a specific object on the marketplace')
            ->setDescription('Lists all available types on the marketplace')
            ->addOption('disable_ssl_verify', 'd', InputOption::VALUE_NONE, 'Disable SSL verification');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rawType = $this->getOptionalArgumentAsString($input, 'type');
        $query = (string) $this->getOptionalArgumentAsString($input, 'query');

        $type = $rawType !== null ? (Service\Marketplace\Type::tryFrom($rawType) ?? Service\Marketplace\Type::APP) : Service\Marketplace\Type::APP;

        $repository = $this->factory->factory($type)->getRepository();

        /** @var MarketplaceActionCollection|MarketplaceAppCollection $collection */
        $collection = $repository->fetchAll(0, $query);

        $rows = [];
        foreach ($collection->getEntry() ?? [] as $object) {
            $rows[] = [$object->getName(), $object->getVersion()];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['Name', 'Version'])
            ->setRows($rows);

        $table->render();

        return self::SUCCESS;
    }
}
