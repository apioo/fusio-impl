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
use PSX\Sql\Condition;

/**
 * RateLimit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class RateLimit
{
    /**
     * @var \Fusio\Impl\Table\Rate\Plan
     */
    protected $ratePlanTable;

    /**
     * @var \Fusio\Impl\Table\Rate\Allocation
     */
    protected $rateAllocationTable;

    /**
     * @var \Fusio\Impl\Table\Log
     */
    protected $logTable;

    /**
     * RateLimit constructor.
     * @param \Fusio\Impl\Table\Rate\Plan $ratePlanTable
     * @param \Fusio\Impl\Table\Rate\Allocation $rateAllocationTable
     * @param \Fusio\Impl\Table\Log $logTable
     */
    public function __construct(Table\Rate\Plan $ratePlanTable, Table\Rate\Allocation $rateAllocationTable, Table\Log $logTable)
    {
        $this->ratePlanTable = $ratePlanTable;
        $this->rateAllocationTable = $rateAllocationTable;
        $this->logTable = $logTable;
    }

    /**
     * @param string $ip
     * @param integer $routeId
     * @param \Fusio\Engine\Model\App $app
     * @return boolean
     */
    public function hasExceeded($ip, $routeId, Model\App $app)
    {
        $plan = $this->rateAllocationTable->getPlan($routeId, $app);

        if (empty($plan)) {
            return false;
        }

        $count     = (int) $this->getRequestCount($ip, $plan['timespan'], $app);
        $rateLimit = (int) $plan['rateLimit'];

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
}
