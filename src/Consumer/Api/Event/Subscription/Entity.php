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
use Fusio\Impl\Consumer\Model;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\View;
use Fusio\Impl\Model\Message;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Entity
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Entity extends ConsumerApiAbstract
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
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Consumer_Event_Subscription_Entity_Path');
        $path->addInteger('subscription_id');

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::CONSUMER, ['consumer.subscription']);
        $get->addResponse(200, Model\Event_Subscription::class);

        $put = $builder->addMethod('PUT');
        $put->setSecurity(Authorization::CONSUMER, ['consumer.subscription']);
        $put->setRequest(Model\Event_Subscription_Update::class);
        $put->addResponse(200, Message::class);

        $delete = $builder->addMethod('DELETE');
        $delete->setSecurity(Authorization::CONSUMER, ['consumer.subscription']);
        $delete->addResponse(200, Message::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\Event\Subscription::class)->getEntity(
            $this->context->getUserId(),
            (int) $context->getUriFragment('subscription_id')
        );
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->consumerSubscriptionService->update(
            $context->getUriFragment('subscription_id'),
            $record,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Subscription successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->consumerSubscriptionService->delete(
            $context->getUriFragment('subscription_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Subscription successful deleted',
        );
    }
}
