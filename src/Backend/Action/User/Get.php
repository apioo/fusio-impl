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

namespace Fusio\Impl\Backend\Action\User;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\TableManagerInterface;

/**
 * Get
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Get extends ActionAbstract
{
    /**
     * @var View\User
     */
    private $table;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->table = $tableManager->getTable(View\User::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $user = $this->table->getEntity(
            (int) $request->get('user_id')
        );

        if (empty($user)) {
            throw new StatusCode\NotFoundException('Could not find user');
        }

        if ($user['status'] == Table\User::STATUS_DELETED) {
            throw new StatusCode\GoneException('User was deleted');
        }

        return $user;
    }
}
