<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2016 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Service;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Developer
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Developer
{
    /**
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @var \Fusio\Impl\Table\App
     */
    protected $appTable;

    /**
     * @var \Fusio\Impl\Table\User\Scope
     */
    protected $userScopeTable;

    /**
     * @var integer
     */
    protected $appCount;

    /**
     * @var boolean
     */
    protected $appApproval;

    public function __construct(Service\App $appService, Table\App $appTable, Table\Scope $scopeTable, Table\User\Scope $userScopeTable, $appCount, $appApproval)
    {
        $this->appService     = $appService;
        $this->appTable       = $appTable;
        $this->scopeTable     = $scopeTable;
        $this->userScopeTable = $userScopeTable;
        $this->appCount       = $appCount;
        $this->appApproval    = $appApproval;
    }

    public function create($userId, $name, $url, array $scopes = null)
    {
        // validate data
        $this->assertName($name);
        $this->assertUrl($url);

        // check limit of apps which an user can create
        $condition = new Condition();
        $condition->equals('userId', $userId);
        $condition->in('status', [Table\App::STATUS_ACTIVE, Table\App::STATUS_PENDING, Table\App::STATUS_DEACTIVATED]);

        if ($this->appTable->getCount($condition) > $this->appCount) {
            throw new StatusCode\BadRequestException('Maximal amount of apps reached. Please delete another app in order to register a new one');
        }

        $scopes = $this->getValidUserScopes($userId, $scopes);
        if (empty($scopes)) {
            throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
        }

        $this->appService->create(
            $userId,
            $this->appApproval === false ? Table\App::STATUS_ACTIVE : Table\App::STATUS_PENDING,
            $name,
            $url,
            null,
            $scopes
        );
    }

    public function update($userId, $appId, $name, $url, array $scopes = null)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            if ($app['userId'] != $userId) {
                throw new StatusCode\BadRequestException('App does not belong to the user');
            }

            // validate data
            $this->assertName($name);
            $this->assertUrl($url);

            $scopes = $this->getValidUserScopes($userId, $scopes);
            if (empty($scopes)) {
                throw new StatusCode\BadRequestException('Provide at least one valid scope for the app');
            }

            $this->appService->update(
                $appId,
                $app['status'],
                $name,
                $url,
                null,
                $scopes
            );
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    public function delete($userId, $appId)
    {
        $app = $this->appTable->get($appId);

        if (!empty($app)) {
            if ($app['userId'] != $userId) {
                throw new StatusCode\BadRequestException('App does not belong to the user');
            }

            $this->appService->delete($appId);
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    protected function getValidUserScopes($userId, $scopes)
    {
        if (empty($scopes)) {
            return [];
        }

        $userScopes = $this->userScopeTable->getAvailableScopes($userId);
        $scopes     = $this->scopeTable->getValidScopes($scopes);

        // check that the user can assign only the scopes which are also
        // assigned to the user account
        $scopes = array_filter($scopes, function ($scope) use ($userScopes) {
            foreach ($userScopes as $userScope) {
                if ($userScope['id'] == $scope['id']) {
                    return true;
                }
            }
            return false;
        });

        return array_map(function ($scope) {
            return $scope['name'];
        }, $scopes);
    }

    protected function assertName($name)
    {
        if (empty($name)) {
            throw new StatusCode\BadRequestException('Invalid name');
        }
    }

    protected function assertUrl($url)
    {
        if (!empty($url)) {
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new StatusCode\BadRequestException('Invalid url format');
            }
        }
    }
}
