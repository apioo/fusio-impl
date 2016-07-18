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

namespace Fusio\Impl\Service\User;

use PSX\Http\Exception as StatusCode;

/**
 * ValidatorTrait
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait ValidatorTrait
{
    protected function assertName($name)
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name must not be empty');
        }

        $len = strlen($name);

        if ($len < 3) {
            throw new StatusCode\BadRequestException('Name must have at least 3 characters');
        }

        for ($i = 0; $i < $len; $i++) {
            $value = ord($name[$i]);
            if ($value >= 0x21 && $value <= 0x7E) {
            } else {
                throw new StatusCode\BadRequestException('Name must contain only ascii characters in the range of 0x21-0x7E');
            }
        }
    }

    protected function assertEmail($email)
    {
        if (empty($email)) {
            throw new StatusCode\BadRequestException('Email must not be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new StatusCode\BadRequestException('Invalid email format');
        }
    }

    protected function assertPassword($password)
    {
        if (empty($password)) {
            throw new StatusCode\BadRequestException('Password must not be empty');
        }

        $len     = strlen($password);
        $alpha   = 0;
        $numeric = 0;
        $special = 0;

        if ($len < 8) {
            throw new StatusCode\BadRequestException('Password must have at least 8 characters');
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
                throw new StatusCode\BadRequestException('Password must contain only ascii characters in the range of 0x21-0x7E');
            }
        }

        if ($alpha === 0) {
            throw new StatusCode\BadRequestException('Password must have at least one alphabetic character (a-z, A-Z)');
        }

        if ($numeric === 0) {
            throw new StatusCode\BadRequestException('Password must have at least one numeric character (0-9)');
        }

        if ($special === 0) {
            throw new StatusCode\BadRequestException('Password must have at least one special character i.e. (!#$%&*@_~)');
        }
    }
}
