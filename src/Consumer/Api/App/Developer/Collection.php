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

namespace Fusio\Impl\Consumer\Api\App\Developer;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\App\ValidatorTrait;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Schema;
use Fusio\Impl\Consumer\View;
use PSX\Api\Resource;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Collection
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Collection extends ConsumerApiAbstract
{
    use ValidatorTrait;

    /**
     * @Inject
     * @var \Fusio\Impl\Service\App\Developer
     */
    protected $appDeveloperService;

    /**
     * @inheritdoc
     */
    public function getDocumentation($version = null)
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::CONSUMER, ['consumer'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\App\Developer\Collection::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::CONSUMER, ['consumer'])
            ->setRequest($this->schemaManager->getSchema(Schema\App\Developer\Create::class))
            ->addResponse(201, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\App\Developer::class)->getCollection(
            $this->context->getUserId(),
            (int) $context->getParameter('startIndex'),
            $context->getParameter('search') ?: null
        );
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->appDeveloperService->create(
            $record->name,
            $record->url,
            $record->scopes,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'App successful created',
        );
    }
}
