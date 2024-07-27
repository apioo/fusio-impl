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
    private View\Statistic\ErrorsPerOperation $errorsPerOperation;
    private View\Statistic\IncomingRequests $incomingRequests;
    private View\Statistic\IncomingTransactions $incomingTransactions;
    private View\Statistic\MostUsedOperations $mostUsedOperations;
    private View\Statistic\TimePerOperation $timePerOperation;
    private View\Statistic\TestCoverage $testCoverage;
    private View\Dashboard\LatestApps $latestApps;
    private View\Dashboard\LatestRequests $latestRequests;
    private View\Dashboard\LatestUsers $latestUsers;

    public function __construct(TableManagerInterface $tableManager)
    {
        $this->errorsPerOperation = $tableManager->getTable(View\Statistic\ErrorsPerOperation::class);
        $this->incomingRequests = $tableManager->getTable(View\Statistic\IncomingRequests::class);
        $this->incomingTransactions = $tableManager->getTable(View\Statistic\IncomingTransactions::class);
        $this->mostUsedOperations = $tableManager->getTable(View\Statistic\MostUsedOperations::class);
        $this->timePerOperation = $tableManager->getTable(View\Statistic\TimePerOperation::class);
        $this->testCoverage = $tableManager->getTable(View\Statistic\TestCoverage::class);
        $this->latestApps = $tableManager->getTable(View\Dashboard\LatestApps::class);
        $this->latestRequests = $tableManager->getTable(View\Dashboard\LatestRequests::class);
        $this->latestUsers = $tableManager->getTable(View\Dashboard\LatestUsers::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): mixed
    {
        $logFilter = Filter\Log\LogQueryFilter::from($request);
        $transactionFilter = Filter\Transaction\TransactionQueryFilter::from($request);

        return [
            'errorsPerOperation' => $this->errorsPerOperation->getView($logFilter, $context),
            'incomingRequests' => $this->incomingRequests->getView($logFilter, $context),
            'incomingTransactions' => $this->incomingTransactions->getView($transactionFilter, $context),
            'mostUsedOperations' => $this->mostUsedOperations->getView($logFilter, $context),
            'timePerOperation' => $this->timePerOperation->getView($logFilter, $context),
            'testCoverage' => $this->testCoverage->getView($context),
            'latestApps' => $this->latestApps->getView($context),
            'latestRequests' => $this->latestRequests->getView($context),
            'latestUsers' => $this->latestUsers->getView($context),
        ];
    }
}
