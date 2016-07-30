<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <k42b3.x@gmail.com>
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

namespace Fusio\Impl\Template\Parser;

use Fusio\Engine\Template\Parser\SqlInterface;
use Fusio\Impl\Template\Extension;

/**
 * Sql
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Sql extends BaseAbstract implements SqlInterface
{
    protected static $parameters = [];

    public function getSqlParameters()
    {
        $parameters = self::$parameters;
        self::$parameters = [];

        return $parameters;
    }

    protected function getExtension()
    {
        return new Extension\Sql();
    }

    public static function getParameters()
    {
        return self::$parameters;
    }

    /**
     * @internal
     * @param mixed $parameter
     */
    public static function addSqlParameter($parameter)
    {
        self::$parameters[] = $parameter;
    }
}
