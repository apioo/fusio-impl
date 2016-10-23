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

namespace Fusio\Impl\Table\Rate;

use PSX\Sql\TableAbstract;

/**
 * Allocation
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Allocation extends TableAbstract
{
    public function getName()
    {
        return 'fusio_rate_allocation';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'rateId' => self::TYPE_INT,
            'routeId' => self::TYPE_INT,
            'appId' => self::TYPE_INT,
            'authenticated' => self::TYPE_BOOLEAN,
            'parameters' => self::TYPE_VARCHAR,
        );
    }

    public function deleteAllFromRate($rateId)
    {
        $sql = 'DELETE FROM fusio_rate_allocation 
                      WHERE rateId = :rateId';

        $this->connection->executeUpdate($sql, ['rateId' => $rateId]);
    }
}
