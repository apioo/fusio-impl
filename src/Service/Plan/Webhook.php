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

use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\PlanRow;
use Fusio\Impl\Table\Generated\TransactionRow;
use Fusio\Impl\Table\Generated\UserRow;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Webhook
{
    private Table\User $userTable;
    private Table\Plan $planTable;
    private Table\Transaction $transactionTable;

    public function __construct(Table\User $userTable, Table\Plan $planTable, Table\Transaction $transactionTable)
    {
        $this->userTable = $userTable;
        $this->planTable = $planTable;
        $this->transactionTable = $transactionTable;
    }

    public function completed(int $userId, int $planId, string $externalId, int $amountTotal, string $sessionId): void
    {
        $user = $this->planTable->find($userId);
        if (!$user instanceof UserRow) {
            throw new \RuntimeException('Provided user id does not exist');
        }

        $plan = $this->planTable->find($planId);
        if (!$plan instanceof PlanRow) {
            throw new \RuntimeException('Provided plan id does not exist');
        }

        if ($this->shouldReset($plan)) {
            $points = $plan->getPoints();
        } else {
            $points = $user->getPoints() + $plan->getPoints();
        }

        $user->setPlanId($planId);
        $user->setPoints($points);
        $this->userTable->update($user);

        $transaction = new TransactionRow();
        $transaction->setUserId($user->getId());
        $transaction->setPlanId($plan->getId());
        $transaction->setTransactionId($sessionId);
        $transaction->setAmount($amountTotal);
        $transaction->setPoints($plan->getPoints());
        $transaction->setInsertDate(new \DateTime());
        $this->transactionTable->create($transaction);
    }

    public function paid(int $userId, int $amountPaid, string $invoiceId): void
    {
        $user = $this->planTable->find($userId);
        if (!$user instanceof UserRow) {
            return;
        }

        $plan = $this->planTable->find($user->getPlanId());
        if (!$plan instanceof PlanRow) {
            return;
        }

        if ($this->shouldReset($plan)) {
            $points = $plan->getPoints();
        } else {
            $points = $user->getPoints() + $plan->getPoints();
        }

        $user->setPoints($points);
        $this->userTable->update($user);

        $transaction = new TransactionRow();
        $transaction->setUserId($user->getId());
        $transaction->setPlanId($plan->getId());
        $transaction->setTransactionId($invoiceId);
        $transaction->setAmount($amountPaid);
        $transaction->setPoints($plan->getPoints());
        $transaction->setInsertDate(new \DateTime());
        $this->transactionTable->create($transaction);
    }

    public function failed(int $userId): void
    {
        $user = $this->planTable->find($userId);
        if (!$user instanceof UserRow) {
            return;
        }

        $plan = $this->planTable->find($user->getPlanId());
        if (!$plan instanceof PlanRow) {
            return;
        }

        $user->setPlanId(null);
        if ($this->shouldReset($plan)) {
            $user->setPoints(0);
        }
        $this->userTable->update($user);
    }

    private function shouldReset(PlanRow $row): bool
    {
        return true;
    }
}
