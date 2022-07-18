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

namespace Fusio\Impl\Service;

use Doctrine\DBAL\Connection;
use Fusio\Engine\Form;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\Generator\ExecutableInterface;
use Fusio\Engine\Generator\ProviderInterface;
use Fusio\Engine\Generator\Setup;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service\Generator\EntityCreator;
use Fusio\Model\Backend\Generator_Provider;
use Fusio\Model\Backend\Generator_Provider_Config;
use PSX\Http\Exception as StatusCode;

/**
 * Generator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Generator
{
    private Connection $connection;
    private ProviderFactory $providerFactory;
    private EntityCreator $entityCreator;
    private ElementFactoryInterface $elementFactory;

    public function __construct(Connection $connection, ProviderFactory $providerFactory, EntityCreator $entityCreator, ElementFactoryInterface $elementFactory)
    {
        $this->connection = $connection;
        $this->providerFactory = $providerFactory;
        $this->entityCreator = $entityCreator;
        $this->elementFactory = $elementFactory;
    }

    public function create(string $providerName, int $categoryId, Generator_Provider $config, UserContext $context): void
    {
        $setup = new Setup();
        $basePath = $config->getPath();
        $scopes = $config->getScopes();
        $configuration = new Parameters($config->getConfig()->getProperties());

        $provider = $this->getProvider($providerName);
        $provider->setup($setup, $basePath, $configuration);

        $this->connection->beginTransaction();

        try {
            $this->entityCreator->createSchemas($categoryId, $setup->getSchemas(), $context);
            $this->entityCreator->createActions($categoryId, $setup->getActions(), $context);
            $this->entityCreator->createRoutes($categoryId, $setup->getRoutes(), $basePath, $scopes, $context);

            if ($provider instanceof ExecutableInterface) {
                $provider->execute($configuration);
            }

            $this->connection->commit();
        } catch (\Throwable $e) {
            $this->connection->rollBack();

            throw $e;
        }
    }

    public function getForm(string $providerName): Form\Container
    {
        $builder = new Form\Builder();

        $provider = $this->getProvider($providerName);
        $provider->configure($builder, $this->elementFactory);

        return $builder->getForm();
    }

    public function getChangelog(string $providerName, Generator_Provider_Config $config): array
    {
        $setup = new Setup();

        $provider = $this->getProvider($providerName);
        $provider->setup($setup, '/[path]', new Parameters($config->getProperties()));

        return [
            'schemas' => $setup->getSchemas(),
            'actions' => $setup->getActions(),
            'routes' => $setup->getRoutes(),
        ];
    }

    private function getProvider(string $providerName): ProviderInterface
    {
        $provider = $this->providerFactory->factory($providerName);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        return $provider;
    }
}
