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

use Fusio\Engine\Model\ProductInterface;
use Fusio\Impl\Table;

/**
 * Contract
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Contract
{
    /**
     * @var \Fusio\Impl\Table\Plan\Contract
     */
    private $contractTable;

    /**
     * @var \Fusio\Impl\Table\Plan\Invoice
     */
    private $invoiceTable;

    /**
     * @param \Fusio\Impl\Table\Plan\Contract $contractTable
     * @param \Fusio\Impl\Table\Plan\Invoice $invoiceTable
     */
    public function __construct(Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable)
    {
        $this->contractTable = $contractTable;
        $this->invoiceTable  = $invoiceTable;
    }

    /**
     * @param integer $userId
     * @param \Fusio\Engine\Model\ProductInterface $product
     * @return integer
     */
    public function create($userId, ProductInterface $product)
    {
        $interval = $product->getInterval();
        if (empty($interval)) {
            // in case the product has no interval we have a onetime purchase
            $status = Table\Plan\Contract::STATUS_ONETIME;
        } else {
            // otherwise we have a active subscription in a specific interval
            $status = Table\Plan\Contract::STATUS_ACTIVE;
        }

        $this->contractTable->create([
            'user_id' => $userId,
            'plan_id' => $product->getId(),
            'status' => $status,
            'amount' => $product->getPrice(),
            'points' => $product->getPoints(),
            'interval' => $product->getInterval(),
            'insert_date' => new \DateTime(),
        ]);

        $contractId = $this->contractTable->getLastInsertId();

        return $contractId;
    }
}
