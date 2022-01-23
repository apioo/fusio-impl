<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * @link    https://www.fusio-project.org
 */
class DateCalculator
{
    /**
     * Returns the end date of a contract
     */
    public function calculate(\DateTimeInterface $startDate, int $interval): \DateTimeInterface
    {
        switch ($interval) {
            case Table\Plan::INTERVAL_1MONTH:
                return $this->addMonth($startDate, 1);

            case Table\Plan::INTERVAL_3MONTH:
                return $this->addMonth($startDate, 3);

            case Table\Plan::INTERVAL_6MONTH:
                return $this->addMonth($startDate, 6);

            case Table\Plan::INTERVAL_12MONTH:
                return $this->addMonth($startDate, 12);

            case Table\Plan::INTERVAL_NONE:
                return $startDate;
        }

        throw new \InvalidArgumentException('Provided interval does not exist');
    }

    /**
     * Adds the amount of month to the provided date without crossing a date border. I.e. if the date is 31.01 and we
     * add 1 month the end date is 28.02
     */
    private function addMonth(\DateTimeInterface $date, int $count): \DateTimeInterface
    {
        $year  = (int) $date->format('Y');
        $month = (int) $date->format('n');
        $day   = (int) $date->format('j');

        $month = $month + $count;

        $target = \DateTime::createFromInterface($date);
        $target->setDate($year, $month, 1);

        $maxDays = (int) $target->format('t');
        if ($day > $maxDays) {
            $day = $maxDays;
        }

        $target->setDate($year, $month, $day);

        return $target;
    }
}
