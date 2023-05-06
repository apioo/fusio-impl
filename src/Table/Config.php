<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

/**
 * Config
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Config extends Generated\ConfigTable
{
    public const FORM_STRING   = 1;
    public const FORM_BOOLEAN  = 2;
    public const FORM_NUMBER   = 3;
    public const FORM_DATETIME = 4;
    public const FORM_EMAIL    = 5;
    public const FORM_TEXT     = 6;
    public const FORM_SECRET   = 7;

    public function getValue($name)
    {
        return $this->connection->fetchAssociative('SELECT id, value, type FROM fusio_config WHERE name = :name', [
            'name' => $name
        ]);
    }
}
