<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Service\Plan;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Plan_Invoice_Create;
use Fusio\Model\Consumer\Plan_Order_Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Order
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Order
{
    private Contract $contractService;
    private Invoice $invoiceService;
    private Table\Plan $planTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Contract $contractService, Invoice $invoiceService, Table\Plan $planTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->contractService = $contractService;
        $this->invoiceService = $invoiceService;
        $this->planTable = $planTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function order(Plan_Order_Request $order, UserContext $context): array
    {
        $product    = $this->planTable->getProduct($order->getPlanId());
        $contractId = $this->contractService->create($context->getUserId(), $product, $context);

        $invoice = new Plan_Invoice_Create();
        $invoice->setContractId($contractId);
        $invoice->setStartDate(new \DateTime());

        $invoiceId  = $this->invoiceService->create($invoice, $context);

        return [
            'contractId' => $contractId,
            'invoiceId' => $invoiceId,
        ];
    }
}
