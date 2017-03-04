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

namespace Fusio\Impl\Service\App;

use Fusio\Impl\Service\App\Token\QueryFilter;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;

/**
 * Token
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Token
{
    /**
     * @var \Fusio\Impl\Table\App\Token
     */
    protected $tokenTable;

    public function __construct(Table\App\Token $tokenTable)
    {
        $this->tokenTable = $tokenTable;
    }

    public function getAll($startIndex = 0, QueryFilter $filter)
    {
        $condition = $filter->getCondition();

        return new ResultSet(
            $this->tokenTable->getCount($condition),
            $startIndex,
            16,
            $this->tokenTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC,
                $condition,
                Fields::blacklist(['token'])
            )
        );
    }

    public function get($logId)
    {
        $token = $this->tokenTable->get($logId);

        if (!empty($token)) {
            return $token;
        } else {
            throw new StatusCode\NotFoundException('Could not find token');
        }
    }
}
