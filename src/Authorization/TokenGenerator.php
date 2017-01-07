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

namespace Fusio\Impl\Authorization;

use PSX\Framework\Util\Uuid;

/**
 * TokenGenerator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class TokenGenerator
{
    /**
     * Generates the bearer authorization token
     *
     * @return string
     */
    public static function generateToken()
    {
        return implode('-', [
            self::generateString(20),
            self::generateString(48),
            self::generateString(10)
        ]);
    }

    /**
     * Generates the authorization code
     *
     * @return string
     */
    public static function generateCode()
    {
        return self::generateString(16);
    }

    /**
     * Generates the app key
     *
     * @return string
     */
    public static function generateAppKey()
    {
        return Uuid::pseudoRandom();
    }

    /**
     * Generates the app secret
     *
     * @return string
     */
    public static function generateAppSecret()
    {
        return self::generateString(64);
    }

    /**
     * Generates the user password
     *
     * @return string
     */
    public static function generateUserPassword()
    {
        return self::generateString(20);
    }

    private static function generateString($length)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }
}
