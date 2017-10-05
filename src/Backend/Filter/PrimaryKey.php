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

namespace Fusio\Impl\Backend\Filter;

use PSX\Record\RecordInterface;
use PSX\Sql\TableInterface;
use PSX\Validate\FilterAbstract;

/**
 * Path
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class PrimaryKey extends FilterAbstract
{
    /**
     * @var \PSX\Sql\TableInterface
     */
    protected $table;

    /**
     * @param \PSX\Sql\TableInterface $table
     */
    public function __construct(TableInterface $table)
    {
        $this->table = $table;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public function apply($value)
    {
        $id = (int) $value;

        if (empty($id)) {
            return false;
        }

        $record = $this->table->get($id);
        if ($record instanceof RecordInterface) {
            return $id;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return '%s must be a valid primary key';
    }
}
