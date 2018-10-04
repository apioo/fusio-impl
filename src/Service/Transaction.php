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

namespace Fusio\Impl\Service;

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\Transaction as TransactionModel;
use Fusio\Engine\Model\TransactionInterface;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Engine\Payment\RedirectUrls;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Service\Plan\Payer;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;

/**
 * Transaction
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Transaction
{
    /**
     * @var \Fusio\Engine\ConnectorInterface
     */
    protected $connector;

    /**
     * @var \Fusio\Impl\Service\Plan\Payer
     */
    protected $payerService;

    /**
     * @var \Fusio\Impl\Provider\ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var \PSX\Framework\Config\Config $config
     */
    protected $config;

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
     * @param \Fusio\Impl\Provider\ProviderFactory $providerFactory
     * @param \PSX\Framework\Config\Config $config
     * @param \Fusio\Impl\Table\Plan $planTable
     * @param \Fusio\Impl\Table\Plan\Transaction $transactionTable
     */
    public function __construct(ConnectorInterface $connector, Payer $payerService, ProviderFactory $providerFactory, Config $config, Table\Plan $planTable, Table\Plan\Transaction $transactionTable)
    {
        $this->connector = $connector;
        $this->payerService = $payerService;
        $this->providerFactory = $providerFactory;
        $this->config = $config;
        $this->planTable = $planTable;
        $this->transactionTable = $transactionTable;
    }

    /**
     * @param string $name
     * @param integer $planId
     * @param string $returnUrl
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return string
     */
    public function prepare($name, $planId, $returnUrl, UserContext $context)
    {
        /** @var ProviderInterface $provider */
        $provider   = $this->providerFactory->factory($name);
        $product    = $this->createProduct($planId);
        $connection = $this->connector->getConnection($name);

        // create transaction
        $transaction = new TransactionModel();
        $transaction->setPlanId($product->getId());
        $transaction->setUserId($context->getUserId());
        $transaction->setProvider($name);
        $transaction->setTransactionId(Uuid::pseudoRandom());
        $transaction->setReturnUrl($returnUrl);

        $this->transactionTable->beginTransaction();

        try {
            // create transaction
            $this->transactionTable->create([
                'plan_id' => $transaction->getPlanId(),
                'user_id' => $transaction->getUserId(),
                'status' => TransactionModel::STATUS_CREATED,
                'provider' => $transaction->getProvider(),
                'transaction_id' => $transaction->getTransactionId(),
                'amount' => $product->getPrice(),
                'return_url' => $transaction->getReturnUrl(),
                'insert_date' => new \DateTime(),
            ]);

            // prepare payment
            $approvalUrl = $provider->prepare(
                $connection,
                $product,
                $transaction,
                $this->buildRedirectUrls($transaction)
            );

            // update transaction
            $this->updateTransaction($product, $transaction);

            $this->transactionTable->commit();

            return $approvalUrl;
        } catch (\Throwable $e) {
            $this->transactionTable->rollBack();

            throw $e;
        }
    }

    /**
     * @param integer $transactionId
     * @param array $parameters
     * @return string
     */
    public function execute($transactionId, array $parameters)
    {
        $transaction = $this->createTransaction($transactionId);

        /** @var ProviderInterface $provider */
        $provider   = $this->providerFactory->factory($transaction->getProvider());
        $connection = $this->connector->getConnection($transaction->getProvider());

        $this->transactionTable->beginTransaction();

        try {
            // create product
            $product = $this->createProduct($transaction->getPlanId());

            // execute transaction
            $provider->execute($connection, $product, $transaction, $parameters);

            // update transaction
            $this->updateTransaction($product, $transaction);

            $this->transactionTable->commit();

            return $this->buildReturnUrl($transaction);
        } catch (\Throwable $e) {
            $this->transactionTable->rollBack();

            throw $e;
        }
    }

    /**
     * @param integer $planId
     * @return \Fusio\Engine\Model\ProductInterface
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

        $product = new Product();
        $product->setId($plan['id']);
        $product->setName($plan['name']);
        $product->setPrice($plan['price']);
        $product->setPoints($plan['points']);

        return $product;
    }

    /**
     * @param integer $transactionId
     * @return \Fusio\Engine\Model\Transaction
     */
    private function createTransaction($transactionId)
    {
        $condition = new Condition();
        $condition->equals('transaction_id', $transactionId);

        $result = $this->transactionTable->getBy($condition);

        if (empty($result)) {
            throw new StatusCode\BadRequestException('Invalid transaction id');
        }

        if ($result['status'] == TransactionInterface::STATUS_APPROVED) {
            throw new StatusCode\BadRequestException('Transaction is already approved');
        }

        $transaction = new TransactionModel();
        $transaction->setId($result['id']);
        $transaction->setPlanId($result['plan_id']);
        $transaction->setUserId($result['user_id']);
        $transaction->setStatus($result['status']);
        $transaction->setProvider($result['provider']);
        $transaction->setTransactionId($result['transaction_id']);
        $transaction->setAmount($result['amount']);
        $transaction->setReturnUrl($result['return_url']);

        $updateDate = $result['update_date'];
        if (!empty($updateDate)) {
            $transaction->setUpdateDate($updateDate);
        }

        $transaction->setCreateDate($result['insert_date']);

        return $transaction;
    }

    /**
     * @param \Fusio\Engine\Model\ProductInterface $product
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     */
    private function updateTransaction(ProductInterface $product, TransactionInterface $transaction)
    {
        // update transaction
        $this->transactionTable->update([
            'id' => $transaction->getId(),
            'status' => $transaction->getStatus(),
            'remote_id' => $transaction->getRemoteId(),
            'update_date' => new \DateTime(),
        ]);

        // if approved add points to user
        if ($transaction->getStatus() == TransactionInterface::STATUS_APPROVED) {
            $this->payerService->credit($transaction->getUserId(), $product->getPoints());
        }
    }

    /**
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     * @return \Fusio\Engine\Payment\RedirectUrls
     */
    private function buildRedirectUrls(TransactionInterface $transaction)
    {
        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($baseUrl . 'consumer/plan/payment/execute/' . $transaction->getTransactionId());
        $redirectUrls->setCancelUrl($baseUrl . 'consumer/plan/payment/execute/' . $transaction->getTransactionId());

        return $redirectUrls;
    }

    /**
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     * @return string
     */
    private function buildReturnUrl(TransactionInterface $transaction)
    {
        $returnUrl = $transaction->getReturnUrl();
        $returnUrl = str_replace('{transaction_id}', $transaction->getId(), $returnUrl);

        return $returnUrl;
    }
}
