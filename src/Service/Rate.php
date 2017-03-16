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

namespace Fusio\Impl\Service;

use Fusio\Engine\Model;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Sql;

/**
 * Rate
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Rate
{
    /**
     * @var \Fusio\Impl\Table\Rate
     */
    protected $rateTable;

    /**
     * @var \Fusio\Impl\Table\Rate\Allocation
     */
    protected $rateAllocationTable;

    /**
     * @var \Fusio\Impl\Table\Log
     */
    protected $logTable;

    /**
     * @param \Fusio\Impl\Table\Rate $rateTable
     * @param \Fusio\Impl\Table\Rate\Allocation $rateAllocationTable
     * @param \Fusio\Impl\Table\Log $logTable
     */
    public function __construct(Table\Rate $rateTable, Table\Rate\Allocation $rateAllocationTable, Table\Log $logTable)
    {
        $this->rateTable = $rateTable;
        $this->rateAllocationTable = $rateAllocationTable;
        $this->logTable = $logTable;
    }

    public function create($priority, $name, $rateLimit, \DateInterval $timespan, array $allocations = null)
    {
        // check whether rate exists
        $condition  = new Condition();
        $condition->notEquals('status', Table\Rate::STATUS_DELETED);
        $condition->equals('name', $name);

        $app = $this->rateTable->getOneBy($condition);

        if (!empty($app)) {
            throw new StatusCode\BadRequestException('Rate already exists');
        }

        try {
            $this->rateTable->beginTransaction();

            // create rate
            $this->rateTable->create(array(
                'status'    => Table\Rate::STATUS_ACTIVE,
                'priority'  => $priority,
                'name'      => $name,
                'rateLimit' => $rateLimit,
                'timespan'  => $timespan,
            ));

            // get last insert id
            $rateId = $this->rateTable->getLastInsertId();

            $this->handleAllocation($rateId, $allocations);

            $this->rateTable->commit();
        } catch (\Exception $e) {
            $this->rateTable->rollBack();

            throw $e;
        }
    }

    public function update($rateId, $priority, $name, $rateLimit, \DateInterval $timespan, array $allocations = null)
    {
        $rate = $this->rateTable->get($rateId);

        if (!empty($rate)) {
            if ($rate['status'] == Table\Rate::STATUS_DELETED) {
                throw new StatusCode\GoneException('Rate was deleted');
            }

            try {
                $this->rateTable->beginTransaction();

                // update rate
                $this->rateTable->update(array(
                    'id'        => $rate['id'],
                    'priority'  => $priority,
                    'name'      => $name,
                    'rateLimit' => $rateLimit,
                    'timespan'  => $timespan,
                ));

                $this->handleAllocation($rate['id'], $allocations);

                $this->rateTable->commit();
            } catch (\Exception $e) {
                $this->rateTable->rollBack();

                throw $e;
            }
        } else {
            throw new StatusCode\NotFoundException('Could not find rate');
        }
    }

    public function delete($rateId)
    {
        $rate = $this->rateTable->get($rateId);

        if (!empty($rate)) {
            $this->rateTable->update(array(
                'id'     => $rate['id'],
                'status' => Table\Rate::STATUS_DELETED,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find rate');
        }
    }
    
    /**
     * @param string $ip
     * @param integer $routeId
     * @param \Fusio\Engine\Model\App $app
     * @return boolean
     */
    public function hasExceeded($ip, $routeId, Model\App $app)
    {
        $rate = $this->rateAllocationTable->getRateForRequest($routeId, $app);

        if (empty($rate)) {
            return false;
        }

        $count     = (int) $this->getRequestCount($ip, $rate['timespan'], $app);
        $rateLimit = (int) $rate['rateLimit'];

        return $count > $rateLimit;
    }

    /**
     * @param string $ip
     * @param string $timespan
     * @param \Fusio\Engine\Model\App $app
     * @return integer
     */
    protected function getRequestCount($ip, $timespan, Model\App $app)
    {
        if (empty($timespan)) {
            return 0;
        }

        $now  = new \DateTime();
        $past = new \DateTime();
        $past->sub(new \DateInterval($timespan));

        $condition = new Condition();

        if ($app->isAnonymous()) {
            $condition->equals('ip', $ip);
        } else {
            $condition->equals('appId', $app->getId());
        }

        $condition->between('date', $past->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s'));

        return $this->logTable->getCount($condition);
    }

    protected function handleAllocation($rateId, array $allocations = null)
    {
        $this->rateAllocationTable->deleteAllFromRate($rateId);

        if (!empty($allocations)) {
            foreach ($allocations as $allocation) {
                $this->rateAllocationTable->create(array(
                    'rateId'        => $rateId,
                    'routeId'       => $allocation->routeId,
                    'appId'         => $allocation->appId,
                    'authenticated' => $allocation->authenticated,
                    'parameters'    => $allocation->parameters,
                ));
            }
        }
    }
}
