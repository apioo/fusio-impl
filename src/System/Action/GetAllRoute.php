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

namespace Fusio\Impl\System\Action;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use PSX\Sql\TableManagerInterface;

/**
 * GetAllRoute
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class GetAllRoute extends ActionAbstract
{
    private View\Operation $table;

    public function __construct(RuntimeInterface $runtime, TableManagerInterface $tableManager)
    {
        parent::__construct($runtime);

        $this->table = $tableManager->getTable(View\Operation::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        return $this->table->getPublic($request->get('category'));
    }
}
