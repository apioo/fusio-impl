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

namespace Fusio\Impl\Backend\Api\Plan\Invoice;

use Fusio\Impl\Authorization\Authorization;
use Fusio\Impl\Backend\Api\BackendApiAbstract;
use Fusio\Impl\Backend\Model;
use Fusio\Impl\Backend\View;
use PSX\Api\Resource;
use PSX\Api\SpecificationInterface;
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
     * @var \Fusio\Impl\Service\Plan\Invoice
     */
    protected $planInvoiceService;

    /**
     * @inheritdoc
     */
    public function getDocumentation(?string $version = null): ?SpecificationInterface
    {
        $builder = $this->apiManager->getBuilder(Resource::STATUS_ACTIVE, $this->context->getPath());
        $path = $builder->setPathParameters('Plan_Invoice_Entity_Path');
        $path->addInteger('invoice_id');

        $get = $builder->addMethod('GET');
        $get->setSecurity(Authorization::BACKEND, ['backend.plan']);
        $get->addResponse(200, Model\Plan_Invoice::class);

        $put = $builder->addMethod('PUT');
        $put->setSecurity(Authorization::BACKEND, ['backend.plan']);
        $put->setRequest(Model\Plan_Invoice_Update::class);
        $put->addResponse(200, Model\Message::class);

        $delete = $builder->addMethod('DELETE');
        $delete->setSecurity(Authorization::BACKEND, ['backend']);
        $delete->addResponse(200, Model\Message::class);

        return $builder->getSpecification();
    }

    /**
     * @inheritdoc
     */
    protected function doGet(HttpContextInterface $context)
    {
        $plan = $this->tableManager->getTable(View\Plan\Invoice::class)->getEntity(
            (int) $context->getUriFragment('invoice_id')
        );

        if (empty($plan)) {
            throw new StatusCode\NotFoundException('Could not find invoice');
        }

        return $plan;
    }

    /**
     * @inheritdoc
     */
    protected function doPut($record, HttpContextInterface $context)
    {
        $this->planInvoiceService->update(
            (int) $context->getUriFragment('invoice_id'),
            $record->status,
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Invoice successful updated',
        );
    }

    /**
     * @inheritdoc
     */
    protected function doDelete($record, HttpContextInterface $context)
    {
        $this->planInvoiceService->delete(
            (int) $context->getUriFragment('invoice_id'),
            $this->context->getUserContext()
        );

        return array(
            'success' => true,
            'message' => 'Invoice successful deleted',
        );
    }
}
