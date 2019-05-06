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
use Fusio\Engine\Model\Transaction as TransactionModel;
use Fusio\Engine\Model\TransactionInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\Payment\PrepareContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Event\Transaction\ExecutedEvent;
use Fusio\Impl\Event\Transaction\PreparedEvent;
use Fusio\Impl\Event\TransactionEvents;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Table;
use PSX\Framework\Config\Config;
use PSX\Framework\Util\Uuid;
use PSX\Http\Exception as StatusCode;
use PSX\Sql\Condition;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var \Fusio\Impl\Service\Plan\Invoice
     */
    protected $invoiceService;

    /**
     * @var \Fusio\Impl\Provider\ProviderFactory
     */
    protected $providerFactory;

    /**
     * @var \PSX\Framework\Config\Config $config
     */
    protected $config;

    /**
     * @var \Fusio\Impl\Table\Plan\Invoice
     */
    protected $invoiceTable;

    /**
     * @var \Fusio\Impl\Table\Transaction
     */
    protected $transactionTable;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @param \Fusio\Engine\ConnectorInterface $connector
     * @param \Fusio\Impl\Service\Plan\Invoice $invoiceService
     * @param \Fusio\Impl\Provider\ProviderFactory $providerFactory
     * @param \PSX\Framework\Config\Config $config
     * @param \Fusio\Impl\Table\Plan\Invoice $invoiceTable
     * @param \Fusio\Impl\Table\Transaction $transactionTable
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ConnectorInterface $connector, Plan\Invoice $invoiceService, ProviderFactory $providerFactory, Config $config, Table\Plan\Invoice $invoiceTable, Table\Transaction $transactionTable, EventDispatcherInterface $eventDispatcher)
    {
        $this->connector = $connector;
        $this->invoiceService = $invoiceService;
        $this->providerFactory = $providerFactory;
        $this->config = $config;
        $this->invoiceTable = $invoiceTable;
        $this->transactionTable = $transactionTable;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $name
     * @param integer $invoiceId
     * @param string $returnUrl
     * @param \Fusio\Impl\Authorization\UserContext $context
     * @return string
     */
    public function prepare($name, $invoiceId, $returnUrl, UserContext $context)
    {
        $provider = $this->providerFactory->factory($name);

        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $product    = $this->getProduct($invoiceId);
        $connection = $this->connector->getConnection($name);

        // validate return url
        if (empty($returnUrl) || !filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('Invalid return url');
        }

        // create transaction
        $transaction = new TransactionModel();
        $transaction->setInvoiceId($invoiceId);
        $transaction->setProvider($name);
        $transaction->setTransactionId(Uuid::pseudoRandom());
        $transaction->setReturnUrl($returnUrl);

        $this->transactionTable->beginTransaction();

        try {
            // create transaction
            $this->transactionTable->create([
                'invoice_id' => $transaction->getInvoiceId(),
                'status' => TransactionModel::STATUS_CREATED,
                'provider' => $transaction->getProvider(),
                'transaction_id' => $transaction->getTransactionId(),
                'amount' => $product->getPrice(),
                'return_url' => $transaction->getReturnUrl(),
                'insert_date' => new \DateTime(),
            ]);

            // set transaction id
            $transaction->setId($this->transactionTable->getLastInsertId());

            // prepare payment
            $approvalUrl = $provider->prepare(
                $connection,
                $product,
                $transaction,
                $this->buildPrepareContext($transaction)
            );

            // update transaction
            $this->updateTransaction($transaction);

            // trigger event
            $this->eventDispatcher->dispatch(TransactionEvents::PREPARE, new PreparedEvent($transaction));

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
        $provider    = $this->providerFactory->factory($transaction->getProvider());

        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $connection = $this->connector->getConnection($transaction->getProvider());

        $this->transactionTable->beginTransaction();

        try {
            // create product
            $product = $this->getProduct($transaction->getInvoiceId());

            // execute transaction
            $provider->execute($connection, $product, $transaction, new Parameters($parameters));

            // update transaction
            $this->updateTransaction($transaction);

            // trigger event
            $this->eventDispatcher->dispatch(TransactionEvents::EXECUTE, new ExecutedEvent($transaction));

            $this->transactionTable->commit();

            return $this->buildReturnUrl($transaction);
        } catch (\Throwable $e) {
            $this->transactionTable->rollBack();

            throw $e;
        }
    }

    /**
     * @param integer $invoiceId
     * @return \Fusio\Engine\Model\ProductInterface
     */
    private function getProduct($invoiceId)
    {
        $plan = $this->invoiceTable->getPlanByInvoiceId($invoiceId);
        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid invoice id');
        }

        $product = new Product();
        $product->setId($plan['id']);
        $product->setName($plan['name']);
        $product->setPrice($plan['amount']);
        $product->setPoints($plan['points']);
        $product->setInterval($plan['period']);

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

        $result = $this->transactionTable->getOneBy($condition);

        if (empty($result)) {
            throw new StatusCode\BadRequestException('Invalid transaction id');
        }

        if ($result['status'] == TransactionInterface::STATUS_APPROVED) {
            throw new StatusCode\BadRequestException('Transaction is already approved');
        }

        $transaction = new TransactionModel();
        $transaction->setId($result['id']);
        $transaction->setInvoiceId($result['invoice_id']);
        $transaction->setStatus($result['status']);
        $transaction->setProvider($result['provider']);
        $transaction->setTransactionId($result['transaction_id']);
        $transaction->setRemoteId($result['remote_id']);
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
     * Updates the status of the transaction
     * 
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     */
    private function updateTransaction(TransactionInterface $transaction)
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
            $this->invoiceService->pay($transaction);
        }
    }

    /**
     * @param \Fusio\Engine\Model\TransactionInterface $transaction
     * @return \Fusio\Engine\Payment\PrepareContext
     */
    private function buildPrepareContext(TransactionInterface $transaction)
    {
        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');

        $context = new PrepareContext();
        $context->setReturnUrl($baseUrl . 'consumer/transaction/execute/' . $transaction->getTransactionId());
        $context->setCancelUrl($baseUrl . 'consumer/transaction/execute/' . $transaction->getTransactionId());
        $context->setCurrency($this->config->get('fusio_payment_currency'));

        return $context;
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
