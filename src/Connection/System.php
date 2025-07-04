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

namespace Fusio\Impl\Connection;

use Doctrine\DBAL;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractAsset;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Impl\Service\System\FrameworkConfig;

/**
 * System
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class System implements ConnectionInterface, PingableInterface
{
    private FrameworkConfig $frameworkConfig;

    public function __construct(FrameworkConfig $frameworkConfig)
    {
        $this->frameworkConfig = $frameworkConfig;
    }

    public function getName(): string
    {
        return 'System';
    }

    public function getConnection(ParametersInterface $config): DBAL\Connection
    {
        $params = $this->frameworkConfig->getDoctrineConnectionParameters();
        $config = new DBAL\Configuration();
        $config->setSchemaAssetsFilter(static function($assetName) {
            if ($assetName instanceof AbstractAsset) {
                $assetName = $assetName->getName();
            }

            if (in_array($assetName, ['doctrine_migration_versions', 'messenger_messages'])) {
                return false;
            }

            return preg_match('~^(?!fusio_)~', $assetName);
        });

        return DBAL\DriverManager::getConnection($params, $config);
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
    }

    public function ping(mixed $connection): bool
    {
        if ($connection instanceof Connection) {
            try {
                $connection->createSchemaManager()->listTableNames();
                return true;
            } catch (Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }
}
