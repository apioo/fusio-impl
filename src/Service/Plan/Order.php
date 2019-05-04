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

namespace Fusio\Impl\Service\Plan;

use Fusio\Engine\Model\Product;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

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
    protected $contractService;

    /**
     * @var \Fusio\Impl\Service\Plan\Invoice
     */
    protected $invoiceService;

    /**
     * @var \Fusio\Impl\Table\Plan
     */
    protected $planTable;

    /**
     * @param \Fusio\Impl\Service\Plan\Contract $contractService
     * @param \Fusio\Impl\Service\Plan\Invoice $invoiceService
     * @param \Fusio\Impl\Table\Plan $planTable
     */
    public function __construct(Contract $contractService, Invoice $invoiceService, Table\Plan $planTable)
    {
        $this->contractService = $contractService;
        $this->invoiceService = $invoiceService;
        $this->planTable = $planTable;
    }

    /**
     * @param integer $planId
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return array
     */
    public function order($planId, UserContext $context)
    {
        $product    = $this->planTable->getProduct($planId);
        $contractId = $this->contractService->create($context->getUserId(), $product, $context);
        $invoiceId  = $this->invoiceService->create($contractId, new \DateTime(), $context);

        return [
            'contractId' => $contractId,
            'invoiceId' => $invoiceId,
        ];
    }
}
