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

namespace Fusio\Impl\Service\System;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Table;

/**
 * Restorer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Restorer
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function restore(string $type, string $id): int
    {
        return match ($type) {
            'action'     => $this->restoreRecord($id, Table\Generated\ActionTable::COLUMN_NAME, Table\Generated\ActionTable::COLUMN_STATUS, Table\Generated\ActionTable::NAME, Table\Action::STATUS_ACTIVE),
            'app'        => $this->restoreRecord($id, Table\Generated\AppTable::COLUMN_NAME, Table\Generated\AppTable::COLUMN_STATUS, Table\Generated\AppTable::NAME, Table\App::STATUS_ACTIVE),
            'connection' => $this->restoreRecord($id, Table\Generated\ConnectionTable::COLUMN_NAME, Table\Generated\ConnectionTable::COLUMN_STATUS, Table\Generated\ConnectionTable::NAME, Table\Connection::STATUS_ACTIVE),
            'cronjob'    => $this->restoreRecord($id, Table\Generated\CronjobTable::COLUMN_NAME, Table\Generated\CronjobTable::COLUMN_STATUS, Table\Generated\CronjobTable::NAME, Table\Cronjob::STATUS_ACTIVE),
            'routes'     => $this->restoreRecord($id, Table\Generated\RoutesTable::COLUMN_PATH, Table\Generated\RoutesTable::COLUMN_STATUS, Table\Generated\RoutesTable::NAME, Table\Route::STATUS_ACTIVE),
            'schema'     => $this->restoreRecord($id, Table\Generated\SchemaTable::COLUMN_NAME, Table\Generated\SchemaTable::COLUMN_STATUS, Table\Generated\SchemaTable::NAME, Table\Schema::STATUS_ACTIVE),
            'user'       => $this->restoreRecord($id, Table\Generated\UserTable::COLUMN_NAME, Table\Generated\UserTable::COLUMN_STATUS, Table\Generated\UserTable::NAME, Table\User::STATUS_ACTIVE),
            default      => 0,
        };
    }

    private function restoreRecord(string $id, string $nameColumn, string $statusColumn, string $table, int $status): int
    {
        if (is_numeric($id)) {
            $id = (int) $id;
            $nameColumn = 'id';
        }

        return $this->connection->update($table, [
            $statusColumn => $status,
        ], [
            $nameColumn => $id
        ]);
    }
}
