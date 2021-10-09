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

namespace Fusio\Impl\Backend\Action\Action;

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
 * @link    https://www.fusio-project.org
 */
class Get extends ActionAbstract
{
    /**
     * @var View\Action
     */
    private $table;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->table = $tableManager->getTable(View\Action::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $action = $this->table->getEntity(
            $request->get('action_id')
        );

        if (empty($action)) {
            throw new StatusCode\NotFoundException('Could not find action');
        }

        if ($action['status'] == Table\Action::STATUS_DELETED) {
            throw new StatusCode\GoneException('Action was deleted');
        }

        return $action;
    }
}
