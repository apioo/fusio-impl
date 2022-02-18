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
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Fusio\Model\Backend\Plan_Invoice_Create;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * BillingRun
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class BillingRun
{
    private Service\Plan\Invoice $invoiceService;
    private Table\Plan\Contract $contractTable;
    private Table\Plan\Invoice $invoiceTable;
    private Table\User $userTable;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Service\Plan\Invoice $invoiceService, Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable, Table\User $userTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->invoiceService = $invoiceService;
        $this->contractTable = $contractTable;
        $this->invoiceTable = $invoiceTable;
        $this->userTable = $userTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Executes the billing run which creates new invoices if a date border is reached
     */
    public function run(): \Generator
    {
        // get all active contracts
        $contracts = $this->contractTable->getActiveContracts();

        $now = new \DateTime();

        foreach ($contracts as $contract) {
            // skip contracts which have no interval
            if ($contract->getPeriodType() == Table\Plan::INTERVAL_NONE) {
                continue;
            }

            // get last invoice of the contract
            $lastInvoice = $this->invoiceTable->findLastInvoiceByContract($contract->getId());

            $startDate = $lastInvoice === null ? $contract->getInsertDate() : $lastInvoice->getToDate();

            if ($startDate instanceof \DateTime && $startDate < $now) {
                // if the to date is in the past we generate a new invoice for
                // the next time period. This creates a new invoice which the
                // user can pay
                $create = new Plan_Invoice_Create();
                $create->setContractId($contract->getId());
                $create->setStartDate($startDate);
                $invoiceId = $this->invoiceService->create($create, UserContext::newAnonymousContext(), $lastInvoice->getId());

                // @TODO we need a mechanism to reset the points of a user after
                // a billing period. Currently we have more a pay-per-use
                // scenario but if you want to charge a user flat every period
                // we need to reset the points count. The problem here is that
                // the user can then make no more calls at the beginning of the
                // month until he has payed the invoice

                // @TODO we maybe want to send an invoice email to the user.
                // Since we support social logins not every user has an email

                yield $invoiceId;
            }
        }
    }
}
