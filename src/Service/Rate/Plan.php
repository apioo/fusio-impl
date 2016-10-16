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

namespace Fusio\Impl\Service\Rate;

use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Sql;

/**
 * Plan
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Plan
{
    /**
     * @var \Fusio\Impl\Table\Rate\Plan
     */
    protected $planTable;

    public function __construct(Table\Rate\Plan $planTable)
    {
        $this->planTable = $planTable;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        $condition = new Condition();

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        return new ResultSet(
            $this->planTable->getCount($condition),
            $startIndex,
            16,
            $this->planTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC,
                $condition
            )
        );
    }

    public function get($planId)
    {
        $plan = $this->planTable->get($planId);

        if (!empty($plan)) {
            return $plan;
        } else {
            throw new StatusCode\NotFoundException('Could not find plan');
        }
    }

    public function create($name, $rateLimit, $timespan)
    {
        $this->planTable->create(array(
            'name'      => $name,
            'rateLimit' => $rateLimit,
            'timespan'  => $timespan,
        ));
    }

    public function update($planId, $name, $rateLimit, $timespan)
    {
        $plan = $this->planTable->get($planId);

        if (!empty($plan)) {
            $this->planTable->update(array(
                'id'        => $plan['id'],
                'name'      => $name,
                'rateLimit' => $rateLimit,
                'timespan'  => $timespan,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find plan');
        }
    }

    public function delete($planId)
    {
        $plan = $this->planTable->get($planId);

        if (!empty($plan)) {
            $this->planTable->delete(array(
                'id' => $plan['id'],
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find plan');
        }
    }
}
