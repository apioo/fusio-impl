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

namespace Fusio\Impl\Table;

use PSX\Sql\TableAbstract;

/**
 * Routes
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Routes extends TableAbstract
{
    const STATUS_ACTIVE  = 1;
    const STATUS_DELETED = 0;

    public function getName()
    {
        return 'fusio_routes';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'status' => self::TYPE_INT,
            'priority' => self::TYPE_INT,
            'methods' => self::TYPE_VARCHAR,
            'path' => self::TYPE_VARCHAR,
            'controller' => self::TYPE_VARCHAR,
        );
    }
    
    public function getMaxPriority()
    {
        $sql = 'SELECT MAX(priority)
                  FROM fusio_routes 
                 WHERE status = :status 
                   AND priority < 0x1000000';

        $params = [
            'status' => self::STATUS_ACTIVE,
        ];

        return $this->connection->fetchColumn($sql, $params);
    }
}
