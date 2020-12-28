<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2020 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\User;

use PSX\Http\Exception as StatusCode;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Validator
{
    public static function assertName($name)
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name must not be empty');
        }

        if (preg_match('/^[a-zA-Z0-9\-\_\.]{3,32}$/', $name)) {
            return $name;
        } else {
            throw new StatusCode\BadRequestException('Name must be between 3 and 32 signs and use only the characters (a-zA-Z0-9-_.)');
        }
    }

    public static function assertEmail($email)
    {
        if (empty($email)) {
            throw new StatusCode\BadRequestException('Email must not be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new StatusCode\BadRequestException('Invalid email format');
        }
    }

    public static function assertPassword($password, $minLength = null, $minAlpha = null, $minNumeric = null, $minSpecial = null)
    {
        if (empty($password)) {
            throw new StatusCode\BadRequestException('Password must not be empty');
        }

        $minLength  = $minLength ?? 8;
        $minAlpha   = $minAlpha ?? 0;
        $minNumeric = $minNumeric ?? 0;
        $minSpecial = $minSpecial ?? 0;

        // it is not possible to user passwords which have less then 8 chars
        if ($minLength < 8) {
            $minLength = 8;
        }

        $len     = strlen($password);
        $alpha   = 0;
        $numeric = 0;
        $special = 0;

        if ($len < $minLength) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minLength . ' characters');
        }

        for ($i = 0; $i < $len; $i++) {
            $value = ord($password[$i]);
            if ($value >= 0x21 && $value <= 0x7E) {
                if ($value >= 0x30 && $value <= 0x39) {
                    $numeric++;
                } elseif ($value >= 0x41 && $value <= 0x5A) {
                    $alpha++;
                } elseif ($value >= 0x61 && $value <= 0x7A) {
                    $alpha++;
                } else {
                    $special++;
                }
            } else {
                throw new StatusCode\BadRequestException('Password must contain only printable ascii characters (0x21-0x7E)');
            }
        }

        if ($alpha < $minAlpha) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minAlpha . ' alphabetic character (a-z, A-Z)');
        }

        if ($numeric < $minNumeric) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minNumeric . ' numeric character (0-9)');
        }

        if ($special < $minSpecial) {
            throw new StatusCode\BadRequestException('Password must have at least ' . $minSpecial . ' special character i.e. (!#$%&*@_~)');
        }

        return true;
    }
}
