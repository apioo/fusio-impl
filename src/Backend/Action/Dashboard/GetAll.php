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
 * @link    https://www.fusio-project.org
 */
class GetAll extends ActionAbstract
{
    private View\Statistic\ErrorsPerRoute $errorsPerRoute;
    private View\Statistic\IncomingRequests $incomingRequests;
    private View\Statistic\IncomingTransactions $incomingTransactions;
    private View\Statistic\MostUsedRoutes $mostUsedRoutes;
    private View\Statistic\TimePerRoute $timePerRoute;
    private View\Dashboard\LatestApps $latestApps;
    private View\Dashboard\LatestRequests $latestRequests;
    private View\Dashboard\LatestUsers $latestUsers;
    private View\Dashboard\LatestTransactions $latestTransactions;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->errorsPerRoute = $tableManager->getTable(View\Statistic\ErrorsPerRoute::class);
        $this->incomingRequests = $tableManager->getTable(View\Statistic\IncomingRequests::class);
        $this->incomingTransactions = $tableManager->getTable(View\Statistic\IncomingTransactions::class);
        $this->mostUsedRoutes = $tableManager->getTable(View\Statistic\MostUsedRoutes::class);
        $this->timePerRoute = $tableManager->getTable(View\Statistic\TimePerRoute::class);
        $this->latestApps = $tableManager->getTable(View\Dashboard\LatestApps::class);
        $this->latestRequests = $tableManager->getTable(View\Dashboard\LatestRequests::class);
        $this->latestUsers = $tableManager->getTable(View\Dashboard\LatestUsers::class);
        $this->latestTransactions = $tableManager->getTable(View\Dashboard\LatestTransactions::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $logFilter = \Fusio\Impl\Backend\Filter\Log\QueryFilter::create($request);
        $transactionFilter = \Fusio\Impl\Backend\Filter\Transaction\QueryFilter::create($request);

        return [
            'errorsPerRoute' => $this->errorsPerRoute->getView($context->getUser()->getCategoryId(), $logFilter),
            'incomingRequests' => $this->incomingRequests->getView($context->getUser()->getCategoryId(), $logFilter),
            'incomingTransactions' => $this->incomingTransactions->getView($transactionFilter),
            'mostUsedRoutes' => $this->mostUsedRoutes->getView($context->getUser()->getCategoryId(), $logFilter),
            'timePerRoute' => $this->timePerRoute->getView($context->getUser()->getCategoryId(), $logFilter),
            'latestApps' => $this->latestApps->getView(),
            'latestRequests' => $this->latestRequests->getView($context->getUser()->getCategoryId()),
            'latestUsers' => $this->latestUsers->getView(),
            'latestTransactions' => $this->latestTransactions->getView(),
        ];
    }
}
