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

namespace Fusio\Impl\Backend\Action\Plan\Contract;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Plan;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Plan_Contract_Create;
use PSX\Http\Environment\HttpResponse;
use PSX\Sql\TableManagerInterface;

/**
 * Create
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Create extends ActionAbstract
{
    private Plan\Contract $contractService;
    private Table\Plan $table;

    public function __construct(Plan\Contract $contractService, TableManagerInterface $tableManager)
    {
        $this->contractService = $contractService;
        $this->table = $tableManager->getTable(Table\Plan::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $body = $request->getPayload();

        assert($body instanceof Plan_Contract_Create);

        $product = $this->table->getProduct($body->getPlanId());

        $this->contractService->create(
            $body->getUserId(),
            $product,
            UserContext::newActionContext($context)
        );

        return new HttpResponse(201, [], [
            'success' => true,
            'message' => 'Contract successfully created',
        ]);
    }
}
