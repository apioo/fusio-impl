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
 * Preview
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Preview extends Installer
{
    /**
     * @var \Closure
     */
    protected $callback;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param \Closure $callback
     */
    public function __construct(Connection $connection, \Closure $callback)
    {
        parent::__construct($connection);

        $this->callback = $callback;
    }

    /**
     * @inheritdoc
     */
    protected function executeInstall(VersionInterface $version)
    {
    }

    /**
     * @inheritdoc
     */
    protected function executeUpgrade(VersionInterface $version)
    {
    }

    /**
     * @inheritdoc
     */
    protected function executeQueries(VersionInterface $version, $schemaVersion)
    {
        $queries  = $this->getQueries($version);
        $callback = $this->callback;

        foreach ($queries as $query) {
            $callback($query);
        }
    }
}
