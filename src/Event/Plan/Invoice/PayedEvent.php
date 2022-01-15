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

namespace Fusio\Impl\Event\Plan\Invoice;

use Fusio\Engine\Model\TransactionInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\EventAbstract;
use Fusio\Impl\Table\Generated\PlanInvoiceRow;

/**
 * PayedEvent
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class PayedEvent extends EventAbstract
{
    private int $invoiceId;
    private PlanInvoiceRow $invoice;
    private TransactionInterface $transaction;

    public function __construct(int $invoiceId, PlanInvoiceRow $invoice, TransactionInterface $transaction, UserContext $context)
    {
        parent::__construct($context);

        $this->invoiceId   = $invoiceId;
        $this->invoice     = $invoice;
        $this->transaction = $transaction;
    }

    public function getInvoiceId(): int
    {
        return $this->invoiceId;
    }

    public function getInvoice(): PlanInvoiceRow
    {
        return $this->invoice;
    }

    public function getTransaction(): TransactionInterface
    {
        return $this->transaction;
    }
}
