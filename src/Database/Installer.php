<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Database;

use DateTime;
use Doctrine\DBAL\Connection;

/**
 * Installer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Installer
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $schemaVersion
     */
    public function install($schemaVersion)
    {
        $version = self::getVersion($schemaVersion);

        if ($version instanceof VersionInterface) {
            $this->executeQueries($version, $schemaVersion);
            $this->executeInstall($version);
        }
    }

    /**
     * @param string $fromVersion
     * @param string $toVersion
     */
    public function upgrade($fromVersion, $toVersion)
    {
        $indexFrom   = self::getIndexOf($fromVersion);
        $indexTo     = self::getIndexOf($toVersion);
        $upgradePath = self::getPathBetweenVersions($fromVersion, $toVersion);

        foreach ($upgradePath as $schemaVersion) {
            // install version
            $version = self::getVersion($schemaVersion);

            if ($version instanceof VersionInterface) {
                $this->executeQueries($version, $schemaVersion);

                // we execute the upgrade only if we are jumping to a new
                // version
                if ($indexTo > $indexFrom) {
                    $this->executeUpgrade($version);
                }
            }
        }
    }

    /**
     * @param \Fusio\Impl\Database\VersionInterface $version
     */
    private function executeInstall(VersionInterface $version)
    {
        $this->connection->beginTransaction();

        $version->executeInstall($this->connection);

        $this->connection->commit();
    }

    /**
     * @param \Fusio\Impl\Database\VersionInterface $version
     */
    private function executeUpgrade(VersionInterface $version)
    {
        $this->connection->beginTransaction();

        $version->executeUpgrade($this->connection);

        $this->connection->commit();
    }

    /**
     * Executes all queries between the current database schema and the provided
     * version
     * 
     * @param \Fusio\Impl\Database\VersionInterface $version
     * @param string $schemaVersion
     */
    private function executeQueries(VersionInterface $version, $schemaVersion)
    {
        $this->connection->beginTransaction();

        $fromSchema = $this->connection->getSchemaManager()->createSchema();
        $toSchema   = $version->getSchema();
        $queries    = $fromSchema->getMigrateToSql($toSchema, $this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->query($query);
        }

        // insert installation entry
        $now = new DateTime();

        $this->connection->insert('fusio_meta', [
            'version'     => $schemaVersion,
            'installDate' => $now->format('Y-m-d H:i:s'),
        ]);

        $this->connection->commit();
    }

    /**
     * Returns the complete upgrade path
     *
     * @return array
     */
    public static function getUpgradePath()
    {
        return [
            '1.1.7',
            '1.1.6',
            '1.1.5',
            '1.1.4',
            '1.1.3',
            '1.1.2',
            '1.1.1',
            '1.1.0',
            '1.0.9',
            '1.0.8',
            '1.0.7',
            '1.0.6',
            '1.0.5',
            '1.0.4',
            '1.0.3',
            '1.0.2',
            '1.0.1',
            '1.0.0',
            '0.9.9',
            '0.9.8',
            '0.9.7',
            '0.9.6',
            '0.9.5',
            '0.9.4',
            '0.9.3',
            '0.9.2',
            '0.9.1',
            '0.9.0',
            '0.8.0',
        ];
    }

    /**
     * Returns the version object by the provided version string
     *
     * @param string $version
     * @return \Fusio\Impl\Database\VersionInterface
     */
    public static function getVersion($version)
    {
        $version   = str_pad(str_replace('.', '', $version), 3, '0');
        $className = 'Fusio\Impl\Database\Version\Version' . $version;

        if (class_exists($className)) {
            return new $className();
        } else {
            return null;
        }
    }

    /**
     * Returns the latest version
     *
     * @return \Fusio\Impl\Database\VersionInterface
     */
    public static function getLatestVersion()
    {
        $versions = self::getUpgradePath();

        return self::getVersion($versions[0]);
    }

    /**
     * Returns the upgrade path between two versions
     *
     * @param string $fromVersion
     * @param string $toVersion
     * @return array
     */
    public static function getPathBetweenVersions($fromVersion, $toVersion)
    {
        $path      = array_reverse(self::getUpgradePath());
        $indexFrom = self::getIndexOf($fromVersion);
        $indexTo   = self::getIndexOf($toVersion);

        // downgrade is not possible
        if ($indexTo < $indexFrom) {
            return [];
        }

        if (isset($path[$indexTo]) && isset($path[$indexFrom])) {
            return array_slice($path, $indexFrom + 1, $indexTo - $indexFrom);
        }

        return [];
    }

    /**
     * Returns an index of the provided version in the upgrade path
     *
     * @param string $version
     * @return int|null|string
     */
    private static function getIndexOf($version)
    {
        $upgradePath = array_reverse(self::getUpgradePath());
        foreach ($upgradePath as $index => $schemaVersion) {
            if (version_compare($schemaVersion, $version, '==')) {
                return $index;
            }
        }
        return null;
    }
}
