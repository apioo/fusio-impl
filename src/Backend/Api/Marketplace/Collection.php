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

namespace Fusio\Impl\Backend\Api\Marketplace;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Service\Marketplace\App;
use PSX\Api\Resource;
use PSX\Http\Environment\HttpContextInterface;

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

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.marketplace'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Marketplace\Collection::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::BACKEND, ['backend.marketplace'])
            ->setRequest($this->schemaManager->getSchema(Schema\Marketplace\Install::class))
            ->addResponse(201, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $apps = $this->marketplaceRepositoryRemote->fetchAll();
        $result = [];

        foreach ($apps as $remoteApp) {
            $app = $remoteApp->toArray();

            $localApp = $this->marketplaceRepositoryLocal->fetchByName($remoteApp->getName());
            if ($localApp instanceof App) {
                $app['local'] = $localApp->toArray();
                $app['local']['startUrl'] = $this->config->get('fusio_apps_url') . '/' . $localApp->getName();
            }

            $result[$remoteApp->getName()] = $app;
        }

        return [
            'apps' => $result
        ];
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $app = $this->marketplaceInstaller->install(
            $record->name,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App ' . $app->getName() . ' successful installed',
        );
    }
}
