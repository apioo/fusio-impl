<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Plan;

use Fusio\Impl\Table;

/**
 * DateCalculator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class DateCalculator
{
    /**
     * Returns the end date of a contract
     * 
     * @param \DateTime $startDate
     * @param integer $interval
     * @return \DateTime
     */
    public function calculate(\DateTime $startDate, $interval)
    {
        switch ($interval) {
            case Table\Plan::INTERVAL_1MONTH:
                return $this->addMonth($startDate, 1);
                break;

            case Table\Plan::INTERVAL_3MONTH:
                return $this->addMonth($startDate, 3);
                break;

            case Table\Plan::INTERVAL_6MONTH:
                return $this->addMonth($startDate, 6);
                break;

            case Table\Plan::INTERVAL_12MONTH:
                return $this->addMonth($startDate, 12);
                break;
        }

        return $startDate;
    }

    /**
     * Adds the amount of month to the provided date without crossing a date
     * border. I.e. if the date is 31.01 and we add 1 month the end date is
     * 28.02
     * 
     * @param \DateTime $date
     * @param integer $count
     * @return \DateTime
     */
    private function addMonth(\DateTime $date, int $count)
    {
        $year  = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day   = (int) $date->format('j');

        $month = $month + $count;

        $target = clone $date;
        $target->setDate($year, $month, 1);

        $maxDays = (int) $target->format('t');
        if ($day > $maxDays) {
            $day = $maxDays;
        }

        $target->setDate($year, $month, $day);

        return $target;
    }
}
