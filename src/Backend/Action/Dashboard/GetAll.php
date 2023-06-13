<?php
/*
 * Fusio is an open source API management platform which helps to create innovative API solutions.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Backend\Action\Dashboard;

use Fusio\Engine\Action\RuntimeInterface;
use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ActionInterface;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\RequestInterface;
use Fusio\Impl\Backend\Filter;
use Fusio\Impl\Backend\View;
use PSX\Sql\TableManagerInterface;

/**
 * GetAll
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class GetAll implements ActionInterface
{
    private View\Statistic\ErrorsPerOperation $errorsPerRoute;
    private View\Statistic\IncomingRequests $incomingRequests;
    private View\Statistic\IncomingTransactions $incomingTransactions;
    private View\Statistic\MostUsedOperations $mostUsedRoutes;
    private View\Statistic\TimePerOperation $timePerRoute;
    private View\Dashboard\LatestApps $latestApps;
    private View\Dashboard\LatestRequests $latestRequests;
    private View\Dashboard\LatestUsers $latestUsers;
    private View\Dashboard\LatestTransactions $latestTransactions;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->errorsPerRoute = $tableManager->getTable(View\Statistic\ErrorsPerOperation::class);
        $this->incomingRequests = $tableManager->getTable(View\Statistic\IncomingRequests::class);
        $this->incomingTransactions = $tableManager->getTable(View\Statistic\IncomingTransactions::class);
        $this->mostUsedRoutes = $tableManager->getTable(View\Statistic\MostUsedOperations::class);
        $this->timePerRoute = $tableManager->getTable(View\Statistic\TimePerOperation::class);
        $this->latestApps = $tableManager->getTable(View\Dashboard\LatestApps::class);
        $this->latestRequests = $tableManager->getTable(View\Dashboard\LatestRequests::class);
        $this->latestUsers = $tableManager->getTable(View\Dashboard\LatestUsers::class);
        $this->latestTransactions = $tableManager->getTable(View\Dashboard\LatestTransactions::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $logFilter = Filter\Log\QueryFilter::create($request);
        $transactionFilter = Filter\Transaction\QueryFilter::create($request);

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
