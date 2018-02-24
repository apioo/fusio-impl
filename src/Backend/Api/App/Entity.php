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

namespace Fusio\Impl\Backend\Api\App;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Table;
use PSX\Api\Resource;
use PSX\Framework\Loader\Context;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Entity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends BackendApiAbstract
{
    use ValidatorTrait;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\App
     */
    protected $appService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\App::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->setRequest($this->schemaManager->getSchema(Schema\App\Update::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->setSecurity(Authorization::BACKEND, ['backend'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $app = $this->tableManager->getTable(View\App::class)->getEntity(
            (int) $context->getUriFragment('app_id')
        );

        if (!empty($app)) {
            if ($app['status'] == Table\App::STATUS_DELETED) {
                throw new StatusCode\GoneException('App was deleted');
            }

            return $app;
        } else {
            throw new StatusCode\NotFoundException('Could not find app');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->appService->update(
            (int) $context->getUriFragment('app_id'),
            $record->status,
            $record->name,
            $record->url,
            $record->parameters,
            $record->scopes,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->appService->delete(
            (int) $context->getUriFragment('app_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App successful deleted',
        );
    }
}
