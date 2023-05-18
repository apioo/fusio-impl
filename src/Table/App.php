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

use Fusio\Impl\Table\Generated\AppRow;
use Fusio\Impl\Table\Generated\OperationRow;

/**
 * App
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class App extends Generated\AppTable
{
    public const STATUS_ACTIVE      = 0x1;
    public const STATUS_PENDING     = 0x2;
    public const STATUS_DEACTIVATED = 0x3;
    public const STATUS_DELETED     = 0x4;

    public function findOneByIdentifier(string $id): ?AppRow
    {
        if (str_starts_with($id, '~')) {
            return $this->findOneByName(urldecode(substr($id, 1)));
        } else {
            return $this->find((int) $id);
        }
    }
}
