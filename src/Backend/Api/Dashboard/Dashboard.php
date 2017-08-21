<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

use Fusio\Impl\Authorization\ProtectionTrait;
use Fusio\Impl\Backend\View;
use PSX\Framework\Controller\ApiAbstract;

/**
 * Dashboard
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Dashboard extends ApiAbstract
{
    use ProtectionTrait;

    /**
     * @Inject
     * @var \PSX\Sql\TableManager
     */
    protected $tableManager;

    public function onGet()
    {
        $filter = View\Log\QueryFilter::create($this->getParameters());

        $this->setBody([
            'incomingRequests' => $this->tableManager->getTable(View\Statistic::class)->getIncomingRequests($filter),
            'mostUsedRoutes' => $this->tableManager->getTable(View\Statistic::class)->getMostUsedRoutes($filter),
            'timePerRoute' => $this->tableManager->getTable(View\Statistic::class)->getTimePerRoute($filter),
            'latestApps' => $this->tableManager->getTable(View\Dashboard::class)->getLatestApps(),
            'latestRequests' => $this->tableManager->getTable(View\Dashboard::class)->getLatestRequests(),
            'errorsPerRoute' => $this->tableManager->getTable(View\Statistic::class)->getErrorsPerRoute($filter),
        ]);
    }
}
