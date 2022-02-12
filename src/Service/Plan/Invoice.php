<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Plan\Invoice\CreatedEvent;
use Fusio\Impl\Event\Plan\Invoice\DeletedEvent;
use Fusio\Impl\Event\Plan\Invoice\PayedEvent;
use Fusio\Impl\Event\Plan\Invoice\UpdatedEvent;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Plan_Invoice_Create;
use Fusio\Model\Backend\Plan_Invoice_Update;
use PSX\Http\Exception as StatusCode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Invoice
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Invoice
{
    private Table\Plan\Contract $contractTable;
    private Table\Plan\Invoice $invoiceTable;
    private Table\User $userTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable, Table\User $userTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->contractTable = $contractTable;
        $this->invoiceTable = $invoiceTable;
        $this->userTable = $userTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates an invoice for the provided contract id
     */
    public function create(Plan_Invoice_Create $invoice, UserContext $context, ?int $prevId = null): int
    {
        $contract = $this->contractTable->find($invoice->getContractId());
        if (empty($contract)) {
            throw new \InvalidArgumentException('Invalid contract id');
        }

        $from = (clone $invoice->getStartDate())->setTime(0, 0, 0);
        $to   = (new DateCalculator())->calculate($from, $contract['period_type']);

        $displayId = $this->generateInvoiceId($contract['user_id']);

        // create invoice
        try {
            $this->invoiceTable->beginTransaction();

            $record = new Table\Generated\PlanInvoiceRow([
                Table\Generated\PlanInvoiceTable::COLUMN_CONTRACT_ID => $contract->getId(),
                Table\Generated\PlanInvoiceTable::COLUMN_USER_ID => $contract->getUserId(),
                Table\Generated\PlanInvoiceTable::COLUMN_DISPLAY_ID => $displayId,
                Table\Generated\PlanInvoiceTable::COLUMN_PREV_ID => $prevId,
                Table\Generated\PlanInvoiceTable::COLUMN_STATUS => Table\Plan\Invoice::STATUS_OPEN,
                Table\Generated\PlanInvoiceTable::COLUMN_AMOUNT => $contract->getAmount(),
                Table\Generated\PlanInvoiceTable::COLUMN_POINTS => $contract->getPoints(),
                Table\Generated\PlanInvoiceTable::COLUMN_FROM_DATE => $from,
                Table\Generated\PlanInvoiceTable::COLUMN_TO_DATE => $to,
                Table\Generated\PlanInvoiceTable::COLUMN_PAY_DATE => null,
                Table\Generated\PlanInvoiceTable::COLUMN_INSERT_DATE => new \DateTime(),
            ]);

            $this->invoiceTable->create($record);

            $invoiceId = $this->invoiceTable->getLastInsertId();
            $invoice->setId($invoiceId);

            $this->invoiceTable->commit();
        } catch (\Throwable $e) {
            $this->invoiceTable->rollBack();

            throw $e;
        }

        $this->eventDispatcher->dispatch(new CreatedEvent($invoice, $context));

        return $invoiceId;
    }

    public function update(int $invoiceId, Plan_Invoice_Update $invoice, UserContext $context): int
    {
        $existing = $this->invoiceTable->find($invoiceId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find invoice');
        }

        if ($existing->getStatus() == Table\Plan\Invoice::STATUS_DELETED) {
            throw new StatusCode\GoneException('Invoice was deleted');
        }

        // update invoice
        $record = new Table\Generated\PlanInvoiceRow([
            Table\Generated\PlanInvoiceTable::COLUMN_ID => $existing->getId(),
            Table\Generated\PlanInvoiceTable::COLUMN_STATUS => $invoice->getStatus(),
        ]);

        $this->invoiceTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($invoice, $existing, $context));

        return $invoiceId;
    }

    public function delete(int $invoiceId, UserContext $context): int
    {
        $existing = $this->invoiceTable->find($invoiceId);
        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find invoice');
        }

        $record = new Table\Generated\PlanInvoiceRow([
            Table\Generated\PlanInvoiceTable::COLUMN_ID => $existing->getId(),
            Table\Generated\PlanInvoiceTable::COLUMN_STATUS => Table\Plan\Invoice::STATUS_DELETED,
        ]);

        $this->invoiceTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($existing, $context));

        return $invoiceId;
    }

    /**
     * Marks the invoice which is referenced by the transaction as payed. This also credits the points from the invoice
     * to the account of the user
     */
    public function pay(TransactionInterface $transaction)
    {
        $invoice = $this->invoiceTable->find($transaction->getInvoiceId());
        if (empty($invoice)) {
            throw new \RuntimeException('Invalid invoice id');
        }

        if ($invoice->getStatus() == Table\Plan\Invoice::STATUS_PAYED) {
            throw new \InvalidArgumentException('Invoice already payed');
        }

        if ($transaction->getStatus() != TransactionInterface::STATUS_APPROVED) {
            throw new \InvalidArgumentException('Cant mark invoice as payed since the transaction is not approved');
        }

        $contract = $this->contractTable->find($invoice->getContractId());
        if (empty($contract)) {
            throw new \RuntimeException('Invalid contract id');
        }

        if ($contract->getStatus() == Table\Plan\Contract::STATUS_DELETED) {
            throw new \InvalidArgumentException('Contract was deleted');
        } elseif ($contract->getStatus() == Table\Plan\Contract::STATUS_CANCELLED) {
            throw new \InvalidArgumentException('Contract was cancelled');
        } elseif ($contract->getStatus() == Table\Plan\Contract::STATUS_CLOSED) {
            throw new \InvalidArgumentException('Contract was closed');
        }

        // mark invoice as payed
        $record = new Table\Generated\PlanInvoiceRow([
            Table\Generated\PlanInvoiceTable::COLUMN_ID => $invoice->getId(),
            Table\Generated\PlanInvoiceTable::COLUMN_STATUS => Table\Plan\Invoice::STATUS_PAYED,
            Table\Generated\PlanInvoiceTable::COLUMN_PAY_DATE => new \DateTime(),
        ]);

        $this->invoiceTable->update($record);

        // credit points
        $this->userTable->creditPoints($contract->getUserId(), $invoice->getPoints());

        // dispatch payed event
        $context = UserContext::newContext($contract->getUserId(), 2);
        $this->eventDispatcher->dispatch(new PayedEvent($invoice->getId(), $invoice, $transaction, $context));
    }

    /**
     * @param integer $userId
     * @return string
     */
    private function generateInvoiceId(int $userId): string
    {
        $parts = [
            str_pad((string) $userId, 4, '0', STR_PAD_LEFT),
            date('Y'),
            str_pad(substr((string) intval(microtime(true) * 10), -6), 6, '0', STR_PAD_LEFT)
        ];

        return implode('-', $parts);
    }
}
