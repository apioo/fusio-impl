<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Route;

use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    /**
     * @param string $path
     * @return void
     */
    public static function assertPath(string $path): void
    {
        if (empty($path)) {
            throw new StatusCode\BadRequestException('Path must not be empty');
        }

        if (substr($path, 0, 1) != '/') {
            throw new StatusCode\BadRequestException('Path must start with a /');
        }

        $parts = explode('/', $path);
        array_shift($parts); // the first part is always empty

        // it is possible to use the root path /
        if (count($parts) === 1 && $parts[0] === '') {
            return;
        }

        // check reserved segments
        if (in_array(strtolower($parts[0]), self::getReserved())) {
            throw new StatusCode\BadRequestException('Path uses a path segment which is reserved for the system');
        }

        foreach ($parts as $part) {
            if (empty($part)) {
                throw new StatusCode\BadRequestException('Path has an empty path segment');
            }

            if (!preg_match('/^[!-~]+$/', $part)) {
                throw new StatusCode\BadRequestException('Path contains invalid characters inside a path segment');
            }
        }
    }

    /**
     * @return array
     */
    public static function getReserved(): array
    {
        return [
            'backend',
            'consumer',
            'system',
            'authorization',
        ];
    }
}
