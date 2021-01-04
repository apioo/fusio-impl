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

namespace Fusio\Impl\Service\Plan;

use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Backend\Plan_Invoice_Create;
use Fusio\Model\Consumer\Plan_Order_Request;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Order
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Order
{
    /**
     * @var \Fusio\Impl\Service\Plan\Contract
     */
    private $contractService;

    /**
     * @var \Fusio\Impl\Service\Plan\Invoice
     */
    private $invoiceService;

    /**
     * @var \Fusio\Impl\Table\Plan
     */
    private $planTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Service\Plan\Contract $contractService
     * @param \Fusio\Impl\Service\Plan\Invoice $invoiceService
     * @param \Fusio\Impl\Table\Plan $planTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Contract $contractService, Invoice $invoiceService, Table\Plan $planTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->contractService = $contractService;
        $this->invoiceService = $invoiceService;
        $this->planTable = $planTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Plan_Order_Request $order
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return array
     */
    public function order(Plan_Order_Request $order, UserContext $context)
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
