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

namespace Fusio\Impl\Consumer\Api\Transaction;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Consumer\Api\ConsumerApiAbstract;
use Fusio\Impl\Consumer\Schema;
use Fusio\Impl\Consumer\Model;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
use PSX\Http\Environment\HttpContextInterface;

/**
 * Prepare
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Prepare extends ConsumerApiAbstract
{
    /**
     * @Inject
     * @var \Fusio\Impl\Service\Transaction
     */
    protected $transactionService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Consumer_Transaction_Prepare_Path');
        $path->addString('provider');

        $post = $builder->addMethod('POST');
        $post->setSecurity(Authorization::CONSUMER, ['consumer.transaction']);
        $post->setRequest(Model\Consumer_Transaction_Prepare_Request::class);
        $post->addResponse(200, Model\Consumer_Transaction_Prepare_Response::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doPost($record, HttpContextInterface $context)
    {
        $approvalUrl = $this->transactionService->prepare(
            $context->getUriFragment('provider'),
            $record->invoiceId,
            $record->returnUrl,
            $this->context->getUserContext()
        );
        
        return [
            'approvalUrl' => $approvalUrl,
        ];
    }
}
