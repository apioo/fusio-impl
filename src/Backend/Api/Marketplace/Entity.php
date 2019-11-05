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

namespace Fusio\Impl\Backend\Api\Marketplace;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use Fusio\Impl\Service\Marketplace\App;
use PSX\Api\Resource;
use PSX\Http\Environment\HttpContextInterface;
use PSX\Http\Exception as StatusCode;
use PSX\Schema\Property;

/**
 * Entity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends BackendApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Marketplace\RepositoryInterface
     */
    protected $marketplaceRepositoryRemote;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Marketplace\RepositoryInterface
     */
    protected $marketplaceRepositoryLocal;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\Marketplace\Installer
     */
    protected $marketplaceInstaller;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());
        $resource->addPathParameter('app_name', Property::getString());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.marketplace'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Marketplace\LocalApp::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend.marketplace'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('DELETE')
            ->setSecurity(Authorization::BACKEND, ['backend.marketplace'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $localApp = $this->marketplaceRepositoryLocal->fetchByName(
            $context->getUriFragment('app_name')
        );

        if (empty($localApp)) {
            throw new StatusCode\NotFoundException('Could not find local app');
        }

        $remoteApp = $this->marketplaceRepositoryRemote->fetchByName(
            $context->getUriFragment('app_name')
        );

        $app = $localApp->toArray();

        if ($remoteApp instanceof App) {
            $app['remote'] = $remoteApp->toArray();
        }

        return $app;
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $app = $this->marketplaceInstaller->update(
            $context->getUriFragment('app_name'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $app = $this->marketplaceInstaller->remove(
            $context->getUriFragment('app_name'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successful removed',
        );
    }
}
