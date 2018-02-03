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

namespace Fusio\Impl\Table\Routes;

use PSX\Sql\TableAbstract;

/**
 * Response
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Response extends TableAbstract
{
    public function getName()
    {
        return 'fusio_routes_response';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'methodId' => self::TYPE_INT,
            'code' => self::TYPE_INT,
            'response' => self::TYPE_INT,
        );
    }

    public function getResponses($methodId)
    {
        $sql = 'SELECT response.id, 
                       response.code, 
                       response.response 
                  FROM fusio_routes_response response
                 WHERE response.methodId = :id';

        return $this->connection->fetchAll($sql, [
            'id' => $methodId
        ]);
    }

    public function deleteAllFromMethod($methodId)
    {
        $sql = 'DELETE FROM fusio_routes_response
                      WHERE methodId = :id';

        $params = ['id' => $methodId];

        $this->connection->executeQuery($sql, $params);
    }
}
