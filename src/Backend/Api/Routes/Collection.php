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

namespace Fusio\Impl\Backend\Api\Routes;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Schema\Property;

/**
 * Collection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Collection extends BackendApiAbstract
{
    use ValidatorTrait;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Routes
     */
    protected $routesService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.routes'])
            ->addQueryParameter('startIndex', Property::getInteger())
            ->addQueryParameter('count', Property::getInteger())
            ->addQueryParameter('search', Property::getString())
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Routes\Collection::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend.routes'])
            ->setRequest($this->schemaManager->getSchema(Schema\Routes\Create::class))
            ->addResponse(201, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\Routes::class)->getCollection(
            (int) $context->getParameter('startIndex'),
            (int) $context->getParameter('count'),
            $context->getParameter('search')
        );
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->routesService->create(
            $record->priority,
            $record->path,
            $record->controller,
            $record->scopes,
            $record->config,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Route successful created',
        );
    }
}
