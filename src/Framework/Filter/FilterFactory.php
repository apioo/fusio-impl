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

namespace Fusio\Impl\Framework\Filter;

use Doctrine\DBAL\Connection;
use PSX\Api\Listing\FilterFactory as PSXFilterFactory;
use PSX\Api\Listing\FilterInterface;

/**
 * FilterFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class FilterFactory extends PSXFilterFactory
{
    private Connection $connection;
    private bool $loaded = false;

    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    public function getFilter(string $name): ?FilterInterface
    {
        $this->load();
        return parent::getFilter($name);
    }

    private function load()
    {
        if ($this->loaded) {
            return;
        }

        $result = $this->connection->fetchAll('SELECT id, name FROM fusio_category');
        foreach ($result as $row) {
            $this->addFilter($row['name'], new Filter((int) $row['id']));
        }

        $this->loaded = true;
    }
}
