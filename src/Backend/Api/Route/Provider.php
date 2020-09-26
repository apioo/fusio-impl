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

namespace Fusio\Impl\Backend\Api\Route;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Model\Form_Container;
use Fusio\Impl\Model\Message;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

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
     * @var \Fusio\Impl\Service\Route\Provider
     */
    protected $routesProviderService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Route_Provider_Path');
        $path->addString('provider');

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.routes']);
        $query = $get->setQueryParameters('Route_Provider_Query');
        $query->addString('class');
        $get->addResponse(200, Form_Container::class);

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::BACKEND, ['backend.routes']);
        $post->setRequest(Model\Route_Provider::class);
        $post->addResponse(201, Message::class);

        $put = $builder->addMethod('PUT');
        $put->setSecurity(Authorization::BACKEND, ['backend.routes']);
        $put->setRequest(Model\Route_Provider_Config::class);
        $put->addResponse(200, Model\Route_Provider_Changelog::class);

        return $builder->getSpecification();
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
            $record
        );
    }

    /**
     * @inheritdoc
     */
    public function doPost($record, HttpContextInterface $context)
    {
        $this->routesProviderService->create(
            $context->getUriFragment('provider'),
            $record,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Route successful created',
        );
    }
}
