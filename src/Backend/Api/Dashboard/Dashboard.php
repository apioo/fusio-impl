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

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Backend\Schema;
use PSX\Api\Resource;
use PSX\Framework\Loader\Context;

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
     * @param integer $version
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Dashboard\Dashboard::class))
        );

        return $resource;
    }

    public function doGet()
    {
        $filter = View\Log\QueryFilter::create($this->getParameters());

        return [
            'incomingRequests' => $this->tableManager->getTable(View\Statistic::class)->getIncomingRequests($filter),
            'mostUsedRoutes' => $this->tableManager->getTable(View\Statistic::class)->getMostUsedRoutes($filter),
            'timePerRoute' => $this->tableManager->getTable(View\Statistic::class)->getTimePerRoute($filter),
            'latestApps' => $this->tableManager->getTable(View\Dashboard::class)->getLatestApps(),
            'latestRequests' => $this->tableManager->getTable(View\Dashboard::class)->getLatestRequests(),
            'errorsPerRoute' => $this->tableManager->getTable(View\Statistic::class)->getErrorsPerRoute($filter),
        ];
    }
}
