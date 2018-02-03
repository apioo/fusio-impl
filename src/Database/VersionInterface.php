<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Doctrine\DBAL\Connection;

/**
 * VersionInterface
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
interface VersionInterface
{
    /**
     * Returns the schema for this version
     *
     * @return \Doctrine\DBAL\Schema\Schema
     */
    public function getSchema();

    /**
     * Executes additional queries which can update database fields after an
     * install
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function executeInstall(Connection $connection);

    /**
     * Executes additional queries which can update database fields after an
     * upgrade
     *
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function executeUpgrade(Connection $connection);

    /**
     * Returns an associative array where the key is the table name and the
     * value is an array of SQL queries
     *
     * @return array
     */
    public function getInstallInserts();
}
