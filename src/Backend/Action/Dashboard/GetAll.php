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
use Fusio\Impl\Table;
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
     * @var View\Statistic\ErrorsPerRoute
     */
    private $errorsPerRoute;

    /**
     * @var View\Statistic\IncomingRequests
     */
    private $incomingRequests;

    /**
     * @var View\Statistic\IncomingTransactions
     */
    private $incomingTransactions;

    /**
     * @var View\Statistic\MostUsedRoutes
     */
    private $mostUsedRoutes;

    /**
     * @var View\Statistic\TimePerRoute
     */
    private $timePerRoute;

    /**
     * @var View\Dashboard\LatestApps
     */
    private $latestApps;

    /**
     * @var View\Dashboard\LatestRequests
     */
    private $latestRequests;

    /**
     * @var View\Dashboard\LatestUsers
     */
    private $latestUsers;

    /**
     * @var View\Dashboard\LatestTransactions
     */
    private $latestTransactions;

    /**
     * @var Table\User
     */
    private $userTable;

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
        $this->userTable = $tableManager->getTable(Table\User::class);
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context)
    {
        $logFilter = View\Log\QueryFilter::create($request);
        $transactionFilter = View\Transaction\QueryFilter::create($request);

        $categoryId = $this->userTable->getCategoryForUser($context->getUser()->getId());

        return [
            'errorsPerRoute' => $this->errorsPerRoute->getView($categoryId, $logFilter),
            'incomingRequests' => $this->incomingRequests->getView($categoryId, $logFilter),
            'incomingTransactions' => $this->incomingTransactions->getView($transactionFilter),
            'mostUsedRoutes' => $this->mostUsedRoutes->getView($categoryId, $logFilter),
            'timePerRoute' => $this->timePerRoute->getView($categoryId, $logFilter),
            'latestApps' => $this->latestApps->getView(),
            'latestRequests' => $this->latestRequests->getView($categoryId),
            'latestUsers' => $this->latestUsers->getView(),
            'latestTransactions' => $this->latestTransactions->getView(),
        ];
    }
}
