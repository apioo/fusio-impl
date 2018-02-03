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
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Config extends TableAbstract
{
    const FORM_STRING   = 1;
    const FORM_BOOLEAN  = 2;
    const FORM_NUMBER   = 3;
    const FORM_DATETIME = 4;
    const FORM_EMAIL    = 5;
    const FORM_TEXT     = 6;
    const FORM_SECRET   = 7;

    public function getName()
    {
        return 'fusio_config';
    }

    public function getColumns()
    {
        return array(
            'id' => self::TYPE_INT | self::AUTO_INCREMENT | self::PRIMARY_KEY,
            'name' => self::TYPE_VARCHAR,
            'description' => self::TYPE_VARCHAR,
            'type' => self::TYPE_INT,
            'value' => self::TYPE_VARCHAR,
        );
    }

    public function getValue($name)
    {
        return $this->connection->fetchAssoc('SELECT id, value, type FROM fusio_config WHERE name = :name', [
            'name' => $name
        ]);
    }
}
