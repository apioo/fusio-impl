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

namespace Fusio\Impl\Provider;

use Doctrine\DBAL\Connection;

/**
 * ProviderWriter
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class ProviderWriter
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getAvailableTypes(): array
    {
        return [
            ProviderConfig::TYPE_ACTION,
            ProviderConfig::TYPE_CONNECTION,
            ProviderConfig::TYPE_PAYMENT,
            ProviderConfig::TYPE_USER,
            ProviderConfig::TYPE_ROUTES,
        ];
    }

    public function write(array $newConfig): int
    {
        $types    = $this->getAvailableTypes();
        $existing = $this->getExistingClasses();
        $count    = 0;

        foreach ($newConfig as $type => $classes) {
            foreach ($classes as $class) {
                if (!class_exists($class)) {
                    continue;
                }

                if (in_array($class, $existing)) {
                    continue;
                }

                if (!in_array($type, $types)) {
                    continue;
                }

                $count+= $this->connection->insert('fusio_provider', [
                    'type'  => $type,
                    'class' => $class,
                ]);
            }
        }

        return $count;
    }

    private function getExistingClasses(): array
    {
        $classes = [];
        $result  = $this->connection->fetchAll('SELECT class FROM fusio_provider ORDER BY class ASC');
        foreach ($result as $row) {
            $classes[] = $row['class'];
        }

        return $classes;
    }
}
