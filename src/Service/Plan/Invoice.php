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

use Fusio\Engine\Model\TransactionInterface;
use Fusio\Impl\Table;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Invoice
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
     * @var \Fusio\Impl\Table\User
     */
    private $userTable;

    /**
     * @param \Fusio\Impl\Table\Plan\Contract $contractTable
     * @param \Fusio\Impl\Table\Plan\Invoice $invoiceTable
     * @param \Fusio\Impl\Table\User $userTable
     */
    public function __construct(Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable, Table\User $userTable)
    {
        $this->contractTable = $contractTable;
        $this->invoiceTable = $invoiceTable;
        $this->userTable = $userTable;
    }

    /**
     * Creates an invoice for the provided contract id
     * 
     * @param integer $contractId
     * @param \DateTime $startDate
     */
    public function create($contractId, \DateTime $startDate)
    {
        $contract = $this->contractTable->get($contractId);
        if (empty($contract)) {
            throw new \InvalidArgumentException('Invalid contract id');
        }

        $from = (clone $startDate)->setTime(0, 0, 0);
        $to   = (new DateCalculator())->calculate($from, $contract['interval']);

        $this->invoiceTable->create([
            'contract_id' => $contract['id'],
            'transaction_id' => null,
            'status' => Table\Plan\Invoice::STATUS_OPEN,
            'interval' => $contract['interval'],
            'amount' => $contract['price'],
            'points' => $contract['points'],
            'from_date' => $from,
            'to_date' => $to,
            'pay_date' => null,
            'insert_date' => new \DateTime(),
        ]);

        $invoiceId = $this->invoiceTable->getLastInsertId();
        
        
        return $invoiceId;
    }

    /**
     * Marks the invoice which is referenced by the transaction as payed. This
     * also credits the points from the invoice to the account of the user
     * 
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     */
    public function pay(TransactionInterface $transaction)
    {
        $invoice = $this->invoiceTable->get($transaction->getInvoiceId());
        if (empty($invoice)) {
            throw new \InvalidArgumentException('Invalid invoice id');
        }

        if ($invoice['status'] == Table\Plan\Invoice::STATUS_PAYED) {
            throw new \InvalidArgumentException('Invoice already payed');
        }

        if ($transaction->getStatus() != TransactionInterface::STATUS_APPROVED) {
            throw new \InvalidArgumentException('Cant mark invoice as payed since the transaction is not approved');
        }

        // mark invoice as payed
        $this->invoiceTable->update([
            'id' => $invoice['id'],
            'transaction_id' => $transaction->getId(),
            'status' => Table\Plan\Invoice::STATUS_PAYED,
            'pay_date' => new \DateTime(),
        ]);

        // credit points
        $this->userTable->creditPoints($invoice['user_id'], $invoice['points']);

        // dispatch credited event
        //$this->eventDispatcher->dispatch(PlanEvents::CREDIT, new CreditedEvent($points, $context));
    }
}
