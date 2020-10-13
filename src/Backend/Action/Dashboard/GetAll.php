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

namespace Fusio\Impl\Backend\Action\Dashboard;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\View;
use PSX\Sql\TableManagerInterface;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class GetAll extends ActionAbstract
{
    /**
     * @var TableManagerInterface
     */
    private $tableManager;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->tableManager = $tableManager;
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $logFilter = View\Log\QueryFilter::create($request);
        $transactionFilter = View\Transaction\QueryFilter::create($request);

        return [
            'errorsPerRoute' => $this->tableManager->getTable(View\Statistic\ErrorsPerRoute::class)->getView($logFilter),
            'incomingRequests' => $this->tableManager->getTable(View\Statistic\IncomingRequests::class)->getView($logFilter),
            'incomingTransactions' => $this->tableManager->getTable(View\Statistic\IncomingTransactions::class)->getView($transactionFilter),
            'mostUsedRoutes' => $this->tableManager->getTable(View\Statistic\MostUsedRoutes::class)->getView($logFilter),
            'timePerRoute' => $this->tableManager->getTable(View\Statistic\TimePerRoute::class)->getView($logFilter),
            'latestApps' => $this->tableManager->getTable(View\Dashboard\LatestApps::class)->getView(),
            'latestRequests' => $this->tableManager->getTable(View\Dashboard\LatestRequests::class)->getView(),
            'latestUsers' => $this->tableManager->getTable(View\Dashboard\LatestUsers::class)->getView(),
            'latestTransactions' => $this->tableManager->getTable(View\Dashboard\LatestTransactions::class)->getView(),
        ];
    }
}
