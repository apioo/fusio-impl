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

use Fusio\Impl\Table\Generated\CronjobRow;
use Fusio\Impl\Table\Generated\EventRow;
use PSX\Sql\Condition;

/**
 * Event
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Event extends Generated\EventTable
{
    public const STATUS_ACTIVE  = 1;
    public const STATUS_DELETED = 0;

    public function findOneByIdentifier(string $id): ?EventRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByName(urldecode(substr($id, 1)));
        } else {
            return $this->find((int) $id);
        }
    }
}
