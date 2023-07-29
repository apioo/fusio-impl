<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Framework\Api\Scanner;

use Doctrine\DBAL\Connection;
use PSX\Api\Scanner\FilterFactory as PSXFilterFactory;
use PSX\Api\Scanner\FilterInterface;

/**
 * FilterFactory
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
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

    public function getDefault(): ?string
    {
        $this->load();
        return parent::getDefault();
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $first = null;
        $result = $this->connection->fetchAllAssociative('SELECT id, name FROM fusio_category ORDER BY id ASC');
        foreach ($result as $row) {
            if ($first === null) {
                $first = $row['name'];
            }

            $this->addFilter($row['name'], new Filter((int) $row['id']));
        }

        $this->setDefault($first);

        $this->loaded = true;
    }
}
