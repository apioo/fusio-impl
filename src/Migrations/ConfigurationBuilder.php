<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Migrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations;
use Fusio\Engine\ConnectorInterface;

/**
 * ConfigurationBuilder
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class ConfigurationBuilder
{
    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Doctrine\DBAL\Migrations\OutputWriter|null $outputWriter
     * @return \Doctrine\DBAL\Migrations\Configuration\Configuration
     */
    public static function fromSystem(Connection $connection, Migrations\OutputWriter $outputWriter = null)
    {
        $configuration = new Migrations\Configuration\Configuration($connection, $outputWriter);
        $configuration->setName('Fusio migrations');
        $configuration->setMigrationsNamespace('Fusio\\Impl\\Migrations\\Version');
        $configuration->setMigrationsTableName('fusio_migration_versions');
        $configuration->setMigrationsDirectory(__DIR__ . '/Version');

        $configuration->registerMigrationsFromDirectory($configuration->getMigrationsDirectory());

        return $configuration;
    }

    /**
     * @param string $connectionId
     * @param \Fusio\Engine\ConnectorInterface $connector
     * @param \Doctrine\DBAL\Migrations\OutputWriter|null $outputWriter
     * @return \Doctrine\DBAL\Migrations\Configuration\Configuration
     */
    public static function fromConnector($connectionId, ConnectorInterface $connector, Migrations\OutputWriter $outputWriter = null)
    {
        if (is_numeric($connectionId)) {
            throw new \InvalidArgumentException('Connection id must be a name');
        }

        $namespace  = ucfirst(str_replace('-', '', $connectionId));
        $connection = $connector->getConnection($connectionId);

        if (!$connection instanceof Connection) {
            throw new \RuntimeException('Connection must be a doctrine DBAL connection');
        }

        // migrations are inside the user namespace
        $configuration = new Migrations\Configuration\Configuration($connection, $outputWriter);
        $configuration->setName($connectionId . ' migrations');
        $configuration->setMigrationsNamespace(self::getBaseNamespace() . '\\Migrations\\' . $namespace);
        $configuration->setMigrationsTableName(strtolower($namespace) . '_migration_versions');
        $configuration->setMigrationsDirectory(PSX_PATH_SRC . '/Migrations/' . $namespace);

        $configuration->registerMigrationsFromDirectory($configuration->getMigrationsDirectory());

        return $configuration;
    }

    /**
     * This method tries to find the base namespace of the current app. We try
     * to look at the composer.json file and use the first psr-4 autoload key
     * 
     * @return string
     */
    private static function getBaseNamespace()
    {
        // try to determine the namespace of the
        $composerFile = PSX_PATH_SRC . '/../composer.json';
        $composer = \json_decode(\file_get_contents($composerFile), true);

        if (isset($composer['autoload'])) {
            $paths = $composer['autoload']['psr-4'] ?? [];
            $base  = trim(key($paths), '\\');

            if (!empty($base)) {
                return $base;
            }
        }

        throw new \RuntimeException('Could not determine the base namespace');
    }
}
