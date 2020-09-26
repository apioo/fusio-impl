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

namespace Fusio\Impl\Consumer\Api\Plan\Contract;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Model;
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
     * @var \Fusio\Impl\Service\Plan\Order
     */
    protected $planOrderService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::CONSUMER, ['consumer.plan']);
        $get->addResponse(200, Model\Plan_Contract_Collection::class);

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::CONSUMER, ['consumer.plan']);
        $post->setRequest(Model\Plan_Order_Request::class);
        $post->addResponse(201, Model\Plan_Order_Response::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        return $this->tableManager->getTable(View\Plan\Contract::class)->getCollection(
            $this->context->getUserId(),
            (int) $context->getParameter('startIndex')
        );
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        return $this->planOrderService->order(
            $record,
            $this->context->getUserContext()
        );
    }
}
