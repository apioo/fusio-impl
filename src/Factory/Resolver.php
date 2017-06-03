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

namespace Fusio\Impl\Factory;

/**
 * Resolver
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Resolver
{
    const ENGINE_V8  = 'v8';
    const ENGINE_PHP = 'php';

    /**
     * @param string $engine
     * @return \Fusio\Engine\Factory\ResolverInterface|null
     */
    public static function createResolver($engine)
    {
        if ($engine == self::ENGINE_V8) {
            return new Resolver\JavascriptFile();
        } elseif ($engine == self::ENGINE_PHP) {
            return new Resolver\PhpFile();
        }

        // in case we have no defined resolver we use the default resolver
        // which simply uses a PHP class
        return null;
    }
}
