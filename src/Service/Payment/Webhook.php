<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Service\Payment;

use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Payment\WebhookInterface;
use Fusio\Impl\Table;
use Fusio\Impl\Table\Generated\PlanRow;
use Fusio\Impl\Table\Generated\TransactionRow;
use Fusio\Impl\Table\Generated\UserRow;
use PSX\DateTime\LocalDateTime;

/**
 * Webhook
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Webhook implements WebhookInterface
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

    public function completed(int $userId, int $planId, string $customerId, int $amountTotal, string $sessionId): void
    {
        $user = $this->userTable->find($userId);
        if (!$user instanceof UserRow) {
            return;
        }

        $plan = $this->planTable->find($planId);
        if (!$plan instanceof PlanRow) {
            return;
        }

        if ($plan->getPeriodType() === ProductInterface::INTERVAL_SUBSCRIPTION) {
            // we only assign a plan id to the user for subscriptions since we receive later on a paid event which
            // credits the points to the user
            $user->setPlanId($planId);
        } elseif ($plan->getPeriodType() === ProductInterface::INTERVAL_ONETIME) {
            // for one time payments we directly credit the points to the user but we dont assign a plan id to the user
            $user->setPoints($user->getPoints() + $plan->getPoints());
        }

        $user->setExternalId($customerId);
        $this->userTable->update($user);

        if ($plan->getPeriodType() === ProductInterface::INTERVAL_ONETIME) {
            // create transaction only for one time payments
            $transaction = new TransactionRow();
            $transaction->setUserId($user->getId());
            $transaction->setPlanId($plan->getId());
            $transaction->setTransactionId($sessionId);
            $transaction->setAmount($amountTotal);
            $transaction->setPoints($plan->getPoints());
            $transaction->setInsertDate(LocalDateTime::now());
            $this->transactionTable->create($transaction);
        }
    }

    public function paid(string $customerId, int $amountPaid, string $invoiceId, \DateTimeImmutable $periodStart, \DateTimeImmutable $periodEnd): void
    {
        $user = $this->userTable->findOneByExternalId($customerId);
        if (!$user instanceof UserRow) {
            return;
        }

        $plan = $this->planTable->find($user->getPlanId() ?? 0);
        if (!$plan instanceof PlanRow) {
            return;
        }

        $user->setPoints($user->getPoints() + $plan->getPoints());
        $this->userTable->update($user);

        $transaction = new TransactionRow();
        $transaction->setUserId($user->getId());
        $transaction->setPlanId($plan->getId());
        $transaction->setTransactionId($invoiceId);
        $transaction->setAmount($amountPaid);
        $transaction->setPoints($plan->getPoints());
        $transaction->setPeriodStart(LocalDateTime::from($periodStart));
        $transaction->setPeriodEnd(LocalDateTime::from($periodEnd));
        $transaction->setInsertDate(LocalDateTime::now());
        $this->transactionTable->create($transaction);
    }

    public function failed(string $customerId): void
    {
        $user = $this->userTable->findOneByExternalId($customerId);
        if (!$user instanceof UserRow) {
            return;
        }

        $plan = $this->planTable->find($user->getPlanId() ?? 0);
        if (!$plan instanceof PlanRow) {
            return;
        }

        // remove plan from user in case the payment has failed
        $user->setPlanId(null);
        $this->userTable->update($user);
    }
}
