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

use Fusio\Engine\Model;
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

    public function getRate($routeId, Model\App $app)
    {
        $sql = '    SELECT rate.rateLimit,
                           rate.timespan
                      FROM fusio_rate_allocation rateAllocation
                INNER JOIN fusio_rate rate
                        ON rateAllocation.rateId = rate.id 
                     WHERE (rateAllocation.routeId IS NULL OR rateAllocation.routeId = :routeId)
                       AND (rateAllocation.appId IS NULL OR rateAllocation.appId = :appId)
                       AND (rateAllocation.authenticated IS NULL OR rateAllocation.authenticated = :authenticated)';

        $params = [
            'routeId' => $routeId,
            'appId' => $app->getId(),
            'authenticated' => $app->isAnonymous() ? 0 : 1,
        ];

        $parameters = $app->getParameters();
        if (!empty($parameters)) {
            $sql.= ' AND (rateAllocation.parameters IS NULL OR ';
            $sql.= $this->connection->getDatabasePlatform()->getLocateExpression(':parameters', 'rateAllocation.parameters');
            $sql.= ' > 0)';

            $params['parameters'] = http_build_query($parameters, '', '&');
        }

        $sql.= ' ORDER BY rate.priority DESC';

        return $this->connection->fetchAssoc($sql, $params);
    }

    public function deleteAllFromRate($rateId)
    {
        $sql = 'DELETE FROM fusio_rate_allocation 
                      WHERE rateId = :rateId';

        $this->connection->executeUpdate($sql, ['rateId' => $rateId]);
    }
}
