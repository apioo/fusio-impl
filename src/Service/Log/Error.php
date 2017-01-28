<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Log;

use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Sql;

/**
 * Error
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Error
{
    /**
     * @var \Fusio\Impl\Table\Log\Error
     */
    protected $errorTable;

    public function __construct(Table\Log\Error $errorTable)
    {
        $this->errorTable = $errorTable;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        $condition = new Condition();

        if (!empty($search)) {
            $condition->like('message', '%' . $search . '%');
        }

        return new ResultSet(
            $this->errorTable->getCount($condition),
            $startIndex,
            16,
            $this->errorTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC,
                $condition
            )
        );
    }

    public function get($errorId)
    {
        $error = $this->errorTable->get($errorId);

        if (!empty($error)) {
            return $error;
        } else {
            throw new StatusCode\NotFoundException('Could not find error');
        }
    }
}
