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

namespace Fusio\Impl\Backend\Api\Statistic;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Schema\Property;

/**
 * UsedPoints
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class UsedPoints extends BackendApiAbstract
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
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.statistic'])
            ->addQueryParameter('from', Property::getDateTime())
            ->addQueryParameter('to', Property::getDateTime())
            ->addQueryParameter('routeId', Property::getInteger())
            ->addQueryParameter('appId', Property::getInteger())
            ->addQueryParameter('userId', Property::getInteger())
            ->addQueryParameter('search', Property::getString())
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Statistic\Chart::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\Statistic\UsedPoints::class)->getView(
            View\Plan\Usage\QueryFilter::create($context->getParameters())
        );
    }
}
