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

namespace Fusio\Impl\Service\User;

use Fusio\Impl\Backend\Schema;
use PSX\Http\Exception as StatusCode;

/**
 * ValidatorTrait
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
trait ValidatorTrait
{
    protected function assertStatus($status)
    {
        if (preg_match('/^0|1$/', $status)) {
            return (int) $status;
        } else {
            throw new StatusCode\BadRequestException('Status must be either 0 or 1');
        }
    }

    protected function assertName($name)
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Name must not be empty');
        }

        if (preg_match('/^' . Schema\User::NAME_PATTERN . '$/', $name)) {
            return $name;
        } else {
            throw new StatusCode\BadRequestException('Name must be between 3 and 32 signs and use only the characters (a-zA-Z0-9-_.)');
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
}
