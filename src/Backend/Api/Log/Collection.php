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

namespace Fusio\Impl\Backend\Api\Log;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Backend\View\Log\QueryFilter;
use PSX\Api\Resource;
use PSX\Framework\Loader\Context;
use PSX\Schema\Property;
use PSX\Validate\Validate;

/**
 * Collection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Collection extends BackendApiAbstract
{
    /**
     * @param integer $version
     * @return \PSX\Api\Resource
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->get(Context::KEY_PATH));

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addQueryParameter('startIndex', Property::getInteger())
            ->addQueryParameter('count', Property::getInteger())
            ->addQueryParameter('from', Property::getDateTime())
            ->addQueryParameter('to', Property::getDateTime())
            ->addQueryParameter('routeId', Property::getInteger())
            ->addQueryParameter('appId', Property::getInteger())
            ->addQueryParameter('userId', Property::getInteger())
            ->addQueryParameter('ip', Property::getString())
            ->addQueryParameter('userAgent', Property::getString())
            ->addQueryParameter('method', Property::getString())
            ->addQueryParameter('path', Property::getString())
            ->addQueryParameter('header', Property::getString())
            ->addQueryParameter('body', Property::getString())
            ->addQueryParameter('search', Property::getString())
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Log\Collection::class))
        );

        return $resource;
    }

    /**
     * Returns the GET response
     *
     * @return array|\PSX\Record\RecordInterface
     */
    protected function doGet()
    {
        return $this->tableManager->getTable(View\Log::class)->getCollection(
            $this->queryParameters->getProperty('startIndex'),
            $this->queryParameters->getProperty('count'),
            QueryFilter::create($this->queryParameters->getProperties())
        );
    }
}
