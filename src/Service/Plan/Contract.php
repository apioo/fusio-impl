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

use Fusio\Engine\Model\ProductInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Backend\Model\Plan_Contract_Create;
use Fusio\Impl\Backend\Model\Plan_Contract_Update;
use Fusio\Impl\Event\Plan\Contract\CreatedEvent;
use Fusio\Impl\Event\Plan\Contract\DeletedEvent;
use Fusio\Impl\Event\Plan\Contract\UpdatedEvent;
use Fusio\Impl\Event\Plan\ContractEvents;
use Fusio\Impl\Table;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PSX\Http\Exception as StatusCode;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param \Fusio\Impl\Table\Plan\Contract $contractTable
     * @param \Fusio\Impl\Table\Plan\Invoice $invoiceTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Table\Plan\Contract $contractTable, Table\Plan\Invoice $invoiceTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->contractTable   = $contractTable;
        $this->invoiceTable    = $invoiceTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param integer $userId
     * @param \Fusio\Engine\Model\ProductInterface $product
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return integer
     */
    public function create($userId, ProductInterface $product, UserContext $context)
    {
        $record = [
            'user_id' => $userId,
            'plan_id' => $product->getId(),
            'status' => Table\Plan\Contract::STATUS_ACTIVE,
            'amount' => $product->getPrice(),
            'points' => $product->getPoints(),
            'period_type' => $product->getInterval(),
            'insert_date' => new \DateTime(),
        ];

        $this->contractTable->create($record);

        $contractId = $this->contractTable->getLastInsertId();

        $this->eventDispatcher->dispatch(new CreatedEvent($contractId, $record, $context));

        return (int) $contractId;
    }

    /**
     * @param integer $contractId
     * @param integer $planId
     * @param integer $status
     * @param float $amount
     * @param integer $points
     * @param \Fusio\Impl\Authorization\UserContext $context
     */
    public function update(int $contractId, Plan_Contract_Update $contract, UserContext $context)
    {
        $existing = $this->contractTable->get($contractId);

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find contract');
        }

        if ($existing['status'] == Table\Plan\Contract::STATUS_DELETED) {
            throw new StatusCode\GoneException('Contract was deleted');
        }

        // update contract
        $record = [
            'id'      => $existing['id'],
            'plan_id' => $contract->getPlan(),
            'status'  => $contract->getStatus(),
            'amount'  => $contract->getAmount(),
            'points'  => $contract->getPoints(),
        ];

        $this->contractTable->update($record);

        $this->eventDispatcher->dispatch(new UpdatedEvent($contractId, $record, $existing, $context));
    }

    public function delete(int $contractId, UserContext $context)
    {
        $existing = $this->contractTable->get($contractId);

        if (empty($existing)) {
            throw new StatusCode\NotFoundException('Could not find contract');
        }

        $record = [
            'id'     => $existing['id'],
            'status' => Table\Plan\Contract::STATUS_DELETED,
        ];

        $this->contractTable->update($record);

        $this->eventDispatcher->dispatch(new DeletedEvent($contractId, $existing, $context));
    }
}
