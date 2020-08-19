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

namespace Fusio\Impl\Consumer\Api\Event\Subscription;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Schema;
use Fusio\Impl\Consumer\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
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
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Consumer\Subscription
     */
    protected $consumerSubscriptionService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $resource = new Resource(Resource::STATUS_ACTIVE, $this->context->getPath());

        $resource->addMethod(Resource\Factory::getMethod('GET')
            ->setSecurity(Authorization::CONSUMER, ['consumer.subscription'])
            ->addResponse(200, $this->schemaManager->getSchema(Schema\Event\Subscription\Collection::class))
        );

        $resource->addMethod(Resource\Factory::getMethod('POST')
            ->setSecurity(Authorization::CONSUMER, ['consumer.subscription'])
            ->setRequest($this->schemaManager->getSchema(Schema\Event\Subscription\Create::class))
            ->addResponse(201, $this->schemaManager->getSchema(Schema\Message::class))
        );

        return $resource;
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\Event\Subscription::class)->getCollection(
            $this->context->getUserId(),
            (int) $context->getParameter('startIndex')
        );
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $this->consumerSubscriptionService->create(
            $record->event,
            $record->endpoint,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Subscription successful created',
        );
    }
}
