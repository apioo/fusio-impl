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

namespace Fusio\Impl\Service;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Form;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Generator\ExecutableInterface;
use Fusio\Engine\Generator\ProviderInterface;
use Fusio\Engine\Generator\Setup;
use Fusio\Engine\Parameters;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\GeneratorProvider;
use Fusio\Impl\Service\Generator\EntityCreator;
use Fusio\Model\Backend;
use PSX\Http\Exception as StatusCode;

/**
 * Generator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
readonly class Generator
{
    public function __construct(
        private Connection $connection,
        private GeneratorProvider $generatorProvider,
        private EntityCreator $entityCreator,
        private ElementFactoryInterface $elementFactory
    ) {
    }

    public function create(string $providerName, Backend\GeneratorProvider $config, UserContext $context): void
    {
        $setup = new Setup();
        $basePath = $config->getPath() ?? '';
        $scopes = $config->getScopes();
        $public = $config->getPublic();
        $configuration = new Parameters($config->getConfig()?->getAll() ?? []);
        $prefix = $this->getPrefix($basePath);

        $provider = $this->getProvider($providerName);
        $provider->setup($setup, $configuration);

        $this->connection->beginTransaction();

        try {
            $this->entityCreator->createSchemas($setup->getSchemas(), $prefix, $context);
            $this->entityCreator->createActions($setup->getActions(), $prefix, $context);
            $this->entityCreator->createOperations($setup->getOperations(), $scopes, $public, $basePath, $prefix, $context);

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }

        // NOTE we intentionally do not execute the execute method inside the transaction since this method most likely
        // will execute schema changes on the db and for mysql we can not wrap those actions in a transaction
        if ($provider instanceof ExecutableInterface) {
            $provider->execute($configuration);
        }
    }

    public function getForm(string $providerName): Form\Container
    {
        $builder = new Form\Builder();

        $provider = $this->getProvider($providerName);
        $provider->configure($builder, $this->elementFactory);

        return $builder->getForm();
    }

    public function getChangelog(string $providerName, Backend\GeneratorProviderConfig $config): array
    {
        $setup = new Setup();

        $provider = $this->getProvider($providerName);
        $provider->setup($setup, new Parameters($config->getAll()));

        return [
            'schemas' => $setup->getSchemas(),
            'actions' => $setup->getActions(),
            'operations' => $setup->getOperations(),
        ];
    }

    private function getProvider(string $providerName): ProviderInterface
    {
        $provider = $this->generatorProvider->getInstance($providerName);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        return $provider;
    }

    private function getPrefix(string $path): string
    {
        return implode('_', array_map('ucfirst', array_filter(explode('/', $path))));
    }
}
