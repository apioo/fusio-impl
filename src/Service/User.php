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

namespace Fusio\Impl\Service;

use Fusio\Impl\Service\Consumer\ProviderInterface;
use Fusio\Impl\Service\User\ValidatorTrait;
use Fusio\Impl\Table;
use PSX\DateTime\DateTime;
use PSX\Http\Exception as StatusCode;
use PSX\Model\Common\ResultSet;
use PSX\Sql\Condition;
use PSX\Sql\Fields;
use PSX\Sql\Sql;

/**
 * User
 *
 * @author  Christoph Kappestein <k42b3.x@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class User
{
    use ValidatorTrait;

    /**
     * @var \Fusio\Impl\Table\Scope
     */
    protected $scopeTable;

    /**
     * @var \Fusio\Impl\Table\User
     */
    protected $userTable;

    /**
     * @var \Fusio\Impl\Table\App
     */
    protected $appTable;

    /**
     * @var \Fusio\Impl\Table\User\Scope
     */
    protected $userScopeTable;

    public function __construct(Table\User $userTable, Table\Scope $scopeTable, Table\App $appTable, Table\User\Scope $userScopeTable)
    {
        $this->userTable      = $userTable;
        $this->scopeTable     = $scopeTable;
        $this->appTable       = $appTable;
        $this->userScopeTable = $userScopeTable;
    }

    public function getAll($startIndex = 0, $search = null)
    {
        $condition = new Condition();
        $condition->notEquals('status', Table\User::STATUS_DELETED);

        if (!empty($search)) {
            $condition->like('name', '%' . $search . '%');
        }

        return new ResultSet(
            $this->userTable->getCount($condition),
            $startIndex,
            16,
            $this->userTable->getAll(
                $startIndex,
                16,
                'id',
                Sql::SORT_DESC,
                $condition,
                Fields::blacklist(['password'])
            )
        );
    }

    public function getDetail($userId)
    {
        $user = $this->get($userId);
        $user['scopes'] = $this->userTable->getScopeNames($user['id']);
        $user['apps']   = $this->appTable->getByUserId($user['id'], Fields::blacklist(['userId', 'parameters', 'appSecret']));

        return $user;
    }

    public function get($userId)
    {
        $user = $this->userTable->get($userId, Fields::blacklist(['password']));

        if (!empty($user)) {
            return $user;
        } else {
            throw new StatusCode\NotFoundException('Could not find user');
        }
    }

    /**
     * Authenticates a user based on the username and password. Returns the user
     * id if the authentication was successful else null
     *
     * @param string $username
     * @param string $password
     * @param array $status
     * @return integer|null
     */
    public function authenticateUser($username, $password, array $status)
    {
        if (empty($password)) {
            return null;
        }

        $condition = new Condition();
        $condition->equals('name', $username);
        $condition->in('status', $status);

        $user = $this->userTable->getOneBy($condition);

        if (!empty($user)) {
            // we can authenticate only local users
            if ($user->provider != ProviderInterface::PROVIDER_SYSTEM) {
                return null;
            }

            if ($user->status == Table\User::STATUS_DISABLED) {
                throw new StatusCode\BadRequestException('The assigned account is disabled');
            }

            if ($user->status == Table\User::STATUS_DELETED) {
                throw new StatusCode\BadRequestException('The assigned account is deleted');
            }

            // check password
            if (password_verify($password, $user['password'])) {
                return $user['id'];
            }
        }

        return null;
    }

    public function create($status, $name, $email, $password, array $scopes = null)
    {
        // check whether user exists
        $condition  = new Condition();
        $condition->notEquals('status', Table\User::STATUS_DELETED);
        $condition->equals('name', $name);

        $user = $this->userTable->getOneBy($condition);

        if (!empty($user)) {
            throw new StatusCode\BadRequestException('User already exists');
        }

        // check values
        $this->assertName($name);
        $this->assertEmail($email);
        $this->assertPassword($password);

        try {
            $this->userTable->beginTransaction();

            // create user
            $this->userTable->create(array(
                'provider' => ProviderInterface::PROVIDER_SYSTEM,
                'status'   => $status,
                'name'     => $name,
                'email'    => $email,
                'password' => $password !== null ? \password_hash($password, PASSWORD_DEFAULT) : null,
                'date'     => new DateTime(),
            ));

            $userId = $this->userTable->getLastInsertId();

            // add scopes
            $this->insertScopes($userId, $scopes);

            $this->userTable->commit();
        } catch (\Exception $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        return $userId;
    }

    public function createRemote($provider, $id, $name, $email, array $scopes = null)
    {
        // check whether user exists
        $condition  = new Condition();
        $condition->equals('provider', $provider);
        $condition->equals('remoteId', $id);

        $user = $this->userTable->getOneBy($condition);

        if (!empty($user)) {
            return $user->id;
        }

        // replace spaces with a dot
        $name = str_replace(' ', '.', $name);

        // check values
        $this->assertName($name);

        if (!empty($email)) {
            $this->assertEmail($email);
        } else {
            $email = null;
        }

        try {
            $this->userTable->beginTransaction();

            // create user
            $this->userTable->create(array(
                'provider' => $provider,
                'status'   => Table\User::STATUS_CONSUMER,
                'remoteId' => $id,
                'name'     => $name,
                'email'    => $email,
                'password' => null,
                'date'     => new DateTime(),
            ));

            $userId = $this->userTable->getLastInsertId();

            // add scopes
            $this->insertScopes($userId, $scopes);

            $this->userTable->commit();
        } catch (\Exception $e) {
            $this->userTable->rollBack();

            throw $e;
        }

        return $userId;
    }

    public function update($userId, $status, $name, $email, array $scopes = null)
    {
        $user = $this->userTable->get($userId);

        if (!empty($user)) {
            // check values
            $this->assertName($name);
            $this->assertEmail($email);

            try {
                $this->userTable->beginTransaction();

                $this->userTable->update(array(
                    'id'     => $user['id'],
                    'status' => $status,
                    'name'   => $name,
                    'email'  => $email,
                ));

                // delete existing scopes
                $this->userScopeTable->deleteAllFromUser($user['id']);

                // add scopes
                $this->insertScopes($user['id'], $scopes);

                $this->userTable->commit();
            } catch (\Exception $e) {
                $this->userTable->rollBack();

                throw $e;
            }
        } else {
            throw new StatusCode\NotFoundException('Could not find user');
        }
    }

    public function updateMeta($userId, $email)
    {
        $user = $this->userTable->get($userId);

        if (!empty($user)) {
            // check values
            $this->assertEmail($email);

            $this->userTable->update(array(
                'id'    => $user['id'],
                'email' => $email,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find user');
        }
    }

    public function delete($userId)
    {
        $user = $this->userTable->get($userId);

        if (!empty($user)) {
            $this->userTable->update(array(
                'id'     => $user['id'],
                'status' => Table\User::STATUS_DELETED,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find user');
        }
    }

    public function changeStatus($userId, $status)
    {
        $user = $this->userTable->get($userId);

        if (!empty($user)) {
            $this->userTable->update(array(
                'id'     => $user['id'],
                'status' => $status,
            ));
        } else {
            throw new StatusCode\NotFoundException('Could not find user');
        }
    }

    public function changePassword($userId, $appId, $oldPassword, $newPassword, $verifyPassword)
    {
        // we can only change the password through the backend app
        if (!in_array($appId, [1, 2])) {
            throw new StatusCode\BadRequestException('Changing the password is only possible through the backend or consumer app');
        }

        if (empty($newPassword)) {
            throw new StatusCode\BadRequestException('New password must not be empty');
        }

        if (empty($oldPassword)) {
            throw new StatusCode\BadRequestException('Old password must not be empty');
        }

        // check verify password
        if ($newPassword != $verifyPassword) {
            throw new StatusCode\BadRequestException('New password does not match the verify password');
        }

        // change password
        $result = $this->userTable->changePassword($userId, $oldPassword, $newPassword);

        if ($result) {
            return true;
        } else {
            throw new StatusCode\BadRequestException('Changing password failed');
        }
    }

    public function getValidScopes($userId, array $scopes, array $exclude = array())
    {
        return $this->userScopeTable->getValidScopes($userId, $scopes, $exclude);
    }

    public function getAvailableScopes($userId)
    {
        return $this->userScopeTable->getAvailableScopes($userId);
    }

    protected function insertScopes($userId, $scopes)
    {
        if (!empty($scopes) && is_array($scopes)) {
            $scopes = $this->scopeTable->getByNames($scopes);

            foreach ($scopes as $scope) {
                $this->userScopeTable->create(array(
                    'userId'  => $userId,
                    'scopeId' => $scope['id'],
                ));
            }
        }
    }
}
