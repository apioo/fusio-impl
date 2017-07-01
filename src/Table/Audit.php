<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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
 * Audit
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Audit extends TableAbstract
{
    public function getName()
    {
        return 'fusio_audit';
    }

    public function getColumns()
    {
        return array(
            'audit.id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'audit.appId' => self::TYPE_INT,
            'app.status AS appStatus' => self::TYPE_INT,
            'app.name AS appName' => self::TYPE_VARCHAR,
            'audit.userId' => self::TYPE_INT,
            'userr.status AS userStatus' => self::TYPE_INT,
            'userr.name AS userName' => self::TYPE_VARCHAR,
            'audit.event' => self::TYPE_VARCHAR,
            'audit.ip' => self::TYPE_VARCHAR,
            'audit.date' => self::TYPE_DATETIME,
        );
    }

    protected function newQueryBuilder($table)
    {
        return $this->connection->createQueryBuilder()
            ->from($table, 'audit')
            ->innerJoin('audit', 'fusio_user', 'userr', 'audit.userId = userr.id')
            ->innerJoin('audit', 'fusio_app', 'app', 'audit.appId = app.id');
    }
}
