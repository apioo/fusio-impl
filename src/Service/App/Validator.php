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

namespace Fusio\Impl\Service\App;

use DateInterval;
use DateTime;
use Fusio\Impl\Authorization\TokenGenerator;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\App\GeneratedTokenEvent;
use Fusio\Impl\Event\App\RemovedTokenEvent;
use Fusio\Impl\Service\Security\JsonWebToken;
use Fusio\Impl\Table;
use Fusio\Model\Backend\App;
use Psr\EventDispatcher\EventDispatcherInterface;
use PSX\DateTime\LocalDateTime;
use PSX\Framework\Config\ConfigInterface;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\OAuth2\AccessToken;
use PSX\Sql\Condition;

/**
 * Validator
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Validator
{
    private Table\App $appTable;
    private Table\User $userTable;

    public function __construct(Table\App $appTable, Table\User $userTable)
    {
        $this->appTable = $appTable;
        $this->userTable = $userTable;
    }

    public function assert(App $app, ?Table\Generated\AppRow $existing = null): void
    {
        $userId = $app->getUserId();
        if ($userId !== null) {
            $this->assertUser($userId);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('App name must not be empty');
            }

            $userId = $existing->getUserId();
        }

        $name = $app->getName();
        if ($name !== null) {
            $this->assertName($name, $userId, $existing);
        } else {
            if ($existing === null) {
                throw new StatusCode\BadRequestException('App name must not be empty');
            }
        }
    }

    private function assertUser(int $userId): void
    {
        $user = $this->userTable->find($userId);
        if (empty($user)) {
            throw new StatusCode\BadRequestException('Provided user id does not exist');
        }
    }

    private function assertName(string $name, int $userId, ?Table\Generated\AppRow $existing = null): void
    {
        if (empty($name) || !preg_match('/^[a-zA-Z0-9\\-\\_]{3,64}$/', $name)) {
            throw new StatusCode\BadRequestException('Invalid action name');
        }

        if ($existing === null || $name !== $existing->getName()) {
            $condition  = Condition::withAnd();
            $condition->equals(Table\Generated\AppTable::COLUMN_USER_ID, $userId);
            $condition->notEquals(Table\Generated\AppTable::COLUMN_STATUS, Table\App::STATUS_DELETED);
            $condition->equals(Table\Generated\AppTable::COLUMN_NAME, $name);

            $existing = $this->appTable->findOneBy($condition);
            if (!empty($existing)) {
                throw new StatusCode\BadRequestException('App already exists');
            }
        }
    }
}
