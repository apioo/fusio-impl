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

namespace Fusio\Impl\Table;

/**
 * User
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class User extends Generated\UserTable
{
    public const STATUS_DISABLED = 2;
    public const STATUS_ACTIVE   = 1;
    public const STATUS_DELETED  = 0;

    public function changePassword($userId, $oldPassword, $newPassword, $verifyOld = true)
    {
        $password = $this->connection->fetchColumn('SELECT password FROM fusio_user WHERE id = :id', ['id' => $userId]);

        if (empty($password)) {
            return false;
        }

        if (!$verifyOld || password_verify($oldPassword, $password)) {
            $this->connection->update('fusio_user', [
                'password' => \password_hash($newPassword, PASSWORD_DEFAULT),
            ], [
                'id' => $userId,
            ]);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @param integer $userId
     * @param integer $points
     */
    public function payPoints($userId, $points)
    {
        $this->connection->executeUpdate('UPDATE fusio_user SET points = COALESCE(points, 0) - :points WHERE id = :id', [
            'id' => $userId,
            'points' => $points,
        ]);
    }

    /**
     * @param integer $userId
     * @param integer $points
     */
    public function creditPoints($userId, $points)
    {
        $this->connection->executeUpdate('UPDATE fusio_user SET points = COALESCE(points, 0) + :points WHERE id = :id', [
            'id' => $userId,
            'points' => $points,
        ]);
    }

    /**
     * Sets a specific user attribute
     * 
     * @param integer $userId
     * @param string $name
     * @param string $value
     */
    public function setAttribute($userId, $name, $value)
    {
        $row = $this->connection->fetchAssoc('SELECT id FROM fusio_user_attribute WHERE user_id = :user_id AND name = :name', ['user_id' => $userId, 'name' => $name]);

        if (empty($row)) {
            $this->connection->insert('fusio_user_attribute', [
                'user_id' => $userId,
                'name' => $name,
                'value' => $value,
            ]);
        } else {
            $this->connection->update('fusio_user_attribute', [
                'user_id' => $userId,
                'name' => $name,
                'value' => $value,
            ], [
                'id' => $row['id']
            ]);
        }
    }
}
