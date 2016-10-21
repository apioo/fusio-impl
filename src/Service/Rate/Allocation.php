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
 * Allocation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Allocation
{
    /**
     * @var \Fusio\Impl\Table\Rate\Allocation
     */
    protected $allocationTable;

    public function __construct(Table\Rate\Allocation $allocationTable)
    {
        $this->allocationTable = $allocationTable;
    }

    public function getAll($startIndex = 0)
    {
        return new ResultSet(
            $this->allocationTable->getCount(),
            $startIndex,
            16,
            $this->allocationTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC
            )
        );
    }

    public function get($appId)
    {
        $app = $this->allocationTable->get($appId);

        if (!empty($app)) {
            return $app;
        } else {
            throw new StatusCode\NotFoundException('Could not find allocation');
        }
    }

    public function create($planId, $routeId, $appId, $authenticated, $parameters)
    {
        $this->allocationTable->create(array(
            'planId'        => $planId,
            'routeId'       => $routeId,
            'appId'         => $appId,
            'authenticated' => $authenticated,
            'parameters'    => $parameters,
        ));
    }

    public function update($allocationId, $planId, $routeId, $appId, $authenticated, $parameters)
    {
        $allocation = $this->allocationTable->get($allocationId);

        if (!empty($allocation)) {
            $this->allocationTable->update(array(
                'id'            => $allocation['id'],
                'planId'        => $planId,
                'routeId'       => $routeId,
                'appId'         => $appId,
                'authenticated' => $authenticated,
                'parameters'    => $parameters,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find allocation');
        }
    }

    public function delete($allocationId)
    {
        $allocation = $this->allocationTable->get($allocationId);

        if (!empty($allocation)) {
            $this->allocationTable->delete(array(
                'id' => $allocation['id'],
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find allocation');
        }
    }
}
