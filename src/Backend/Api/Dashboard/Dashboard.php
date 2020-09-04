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

namespace Fusio\Impl\Backend\Api\Dashboard;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Dashboard
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Dashboard extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.dashboard']);
        $get->addResponse(200, Model\Dashboard::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    public function doGet(HttpContextInterface $context)
    {
        $logFilter = View\Log\QueryFilter::create($context->getParameters());
        $transactionFilter = View\Transaction\QueryFilter::create($context->getParameters());

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
