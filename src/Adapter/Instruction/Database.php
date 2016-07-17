<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Adapter\Instruction;

use Fusio\Impl\Adapter\InstructionAbstract;

/**
 * Database
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Database extends InstructionAbstract
{
    public function getName()
    {
        return 'Database';
    }

    public function getKey()
    {
        return 'database';
    }

    public function getDescription()
    {
        $columns = [];
        foreach ($this->payload->columns as $column) {
            $columns[] = $column->name;
        }

        return $this->payload->name . ' (' . implode(', ', $columns) . ')';
    }

    public function setConnectionId($connectionId)
    {
        $this->payload->connectionId = $connectionId;
    }
}
