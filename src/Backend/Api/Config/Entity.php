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

namespace Fusio\Impl\Backend\Api\Config;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Schema;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
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
     * @var \Fusio\Impl\Service\Config
     */
    protected $configService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());
        $resource->addPathParameter('config_id', Property::getInteger());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::BACKEND, ['backend.config'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Config::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('PUT')
            ->setSecurity(Authorization::BACKEND, ['backend.config'])
            ->setRequest($this->schemaManager->getSchema(Schema\Config\Update::class))
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $config = $this->tableManager->getTable(View\Config::class)->getEntity(
            (int) $context->getUriFragment('config_id')
        );

        if (!empty($config)) {
            return $config;
        } else {
            throw new StatusCode\NotFoundException('Could not find config');
        }
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->configService->update(
            (int) $context->getUriFragment('config_id'),
            $record->value,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Config successful updated',
        );
    }
}
