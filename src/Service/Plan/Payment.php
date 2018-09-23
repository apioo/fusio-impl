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

use Fusio\Engine\ConnectorInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Service\Plan\Model;
use Fusio\Impl\Table;
use PSX\Http\Exception as StatusCode;

/**
 * Payment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Payment
{
    /**
     * @var \Fusio\Impl\Service\Plan\ProviderInterface[]
     */
    protected $providers;

    /**
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    protected $payerService;

    /**
     * @var \Fusio\Impl\Table\Plan
     */
    protected $planTable;

    /**
     * @var \Fusio\Impl\Table\Plan\Transaction
     */
    protected $transactionTable;

    /**
     * @param \Fusio\Engine\ConnectorInterface $connector
     * @param \Fusio\Impl\Service\Plan\Payer $payerService
     * @param \Fusio\Impl\Table\Plan $planTable
     * @param \Fusio\Impl\Table\Plan\Transaction $transactionTable
     */
    public function __construct(ConnectorInterface $connector, Payer $payerService, Table\Plan $planTable, Table\Plan\Transaction $transactionTable)
    {
        $this->connector = $connector;
        $this->payerService = $payerService;
        $this->planTable = $planTable;
        $this->transactionTable = $transactionTable;
    }

    /**
     * @param string $name
     * @param ProviderInterface $provider
     */
    public function addProvider($name, ProviderInterface $provider)
    {
        $this->providers[$name] = $provider;
    }

    /**
     * @param string $name
     * @param integer $planId
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return array
     */
    public function prepare($name, $planId, UserContext $context)
    {
        $provider   = $this->getProvider($name);
        $product    = $this->createProduct($planId);
        $connection = $this->connector->getConnection($name);

        // prepare payment
        $transaction = $provider->prepare($connection, $product);

        // create transaction
        $this->transactionTable->create([
            'plan_id' => $transaction->getPlanId(),
            'user_id' => $context->getUserId(),
            'status' => $transaction->getStatus(),
            'provider' => $name,
            'transaction_id' => $transaction->getTransactionId(),
            'amount' => $transaction->getAmount(),
            'insert_date' => $transaction->getCreateDate(),
        ]);

        // return transaction
        return [
            'id' => $transaction->getId(),
            'status' => $transaction->getStatus(),
            'amount' => $transaction->getAmount(),
            'createDate' => $transaction->getCreateDate(),
            'parameters' => $transaction->getParameters(),
        ];
    }

    /**
     * @param string $name
     * @param mixed $options
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return array
     */
    public function execute($name, $options, UserContext $context)
    {
        $provider   = $this->getProvider($name);
        $connection = $this->connector->getConnection($name);

        // create transaction
        $transaction = $this->createTransaction($options);

        // create product
        $product = $this->createProduct($transaction->getPlanId());

        // execute transaction
        $transaction = $provider->execute($connection, $product, $transaction);

        // update transaction
        $this->transactionTable->update([
            'id' => $transaction->getId(),
            'status' => $transaction->getStatus(),
        ]);

        // if approved add points to user
        if ($transaction == Model\Transaction::STATUS_APPROVED) {
            $this->payerService->credit($transaction['user_id'], $product->getPoints());
        }

        return [
            'status' => $transaction->getStatus(),
        ];
    }

    /**
     * @param string $name
     * @return \Fusio\Impl\Service\Plan\ProviderInterface
     */
    private function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new StatusCode\BadRequestException('Invalid payment provider');
        }

        return $this->providers[$name];
    }

    /**
     * @param integer $planId
     * @return \Fusio\Impl\Service\Plan\Model\Product
     */
    private function createProduct($planId)
    {
        $plan = $this->planTable->get($planId);

        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid plan id');
        }

        if ($plan['status'] != Table\Plan::STATUS_ACTIVE) {
            throw new StatusCode\BadRequestException('Invalid plan status');
        }

        $product = new Model\Product();
        $product->setId($plan['id']);
        $product->setName($plan['name']);
        $product->setPrice($plan['price']);
        $product->setPoints($plan['points']);

        return $product;
    }

    private function createTransaction($options)
    {
        $transactionId = $options['paymentId'];

        if (empty($transactionId)) {
            throw new StatusCode\BadRequestException('No transaction id provided');
        }

        $result = $this->transactionTable->getByTransactionId($transactionId);

        if (empty($result)) {
            throw new StatusCode\BadRequestException('Invalid transaction id');
        }

        if ($result['status'] == Model\Transaction::STATUS_APPROVED) {
            throw new StatusCode\BadRequestException('Transaction is already approved');
        }

        $transaction = new Model\Transaction();
        $transaction->setId($result['id']);
        $transaction->setPlanId($result['plan_id']);
        $transaction->setStatus($result['status']);
        $transaction->setTransactionId($result['transaction_id']);
        $transaction->setAmount($result['amount']);
        $transaction->setCreateDate($result['insert_date']);

        foreach ($options as $key => $value) {
            $transaction->setParameter($key, $value);
        }

        return $transaction;
    }
}
