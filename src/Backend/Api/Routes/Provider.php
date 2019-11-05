<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use PSX\Api\Resource;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Schema\Property;

/**
 * Provider
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Provider extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Routes\Provider
     */
    protected $routesProviderService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());
        $resource->addPathParameter('provider', Property::getString());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.routes'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Form\Container::class))
            ->addQueryParameter('class', Property::getString())
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend.routes'])
            ->setRequest($this->schemaManager->getSchema(Schema\Routes\Provider\Config::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Routes\Provider\Changelog::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend.routes'])
            ->setRequest($this->schemaManager->getSchema(Schema\Routes\Provider::class))
            ->addResponse(201, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function doGet(HttpContextInterface $context)
    {
        return $this->routesProviderService->getForm($context->getUriFragment('provider'));
    }

    /**
     * @inheritdoc
     */
    public function doPut($record, HttpContextInterface $context)
    {
        return $this->routesProviderService->getChangelog(
            $context->getUriFragment('provider'),
            $record->getProperties()
        );
    }

    /**
     * @inheritdoc
     */
    public function doPost($record, HttpContextInterface $context)
    {
        $this->routesProviderService->create(
            $context->getUriFragment('provider'),
            $record->path,
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
