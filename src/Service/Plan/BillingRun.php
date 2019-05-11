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

use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * BillingRun
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class BillingRun
{
    /**
     * @var \Fusio\Impl\Service\Plan\Invoice
     */
    private $invoiceService;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Impl\Service\Plan\Invoice $invoiceService
     * @param \Fusio\Impl\Table\Plan\Contract $contractTable
     * @param \Fusio\Impl\Table\Plan\Invoice $invoiceTable
     * @param \Fusio\Impl\Table\User $userTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Service\Plan\Invoice $invoiceService, Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable, Table\User $userTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->invoiceService = $invoiceService;
        $this->contractTable = $contractTable;
        $this->invoiceTable = $invoiceTable;
        $this->userTable = $userTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Executes the billing run which creates new invoices if a date border is
     * reached
     */
    public function run()
    {
        // get all active contracts
        $contracts = $this->contractTable->getActiveContracts();

        $now = new \DateTime();

        foreach ($contracts as $contract) {
            // get last invoice of the contract
            $invoice = $this->invoiceTable->getLastInvoiceByContract($contract['id']);

            $to = $invoice['to_date'];
            if ($to instanceof \DateTime && $to < $now) {
                // if the to date is in the past we generate a new invoice for
                // the next time period. THis creates a new invoice which the
                // user can pay
                $this->invoiceService->create($contract['id'], $to, UserContext::newAnonymousContext(), $invoice['id']);

                // @TODO we maybe want to send an invoice email to the user
            }
        }
    }
}
