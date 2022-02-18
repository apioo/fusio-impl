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

namespace Fusio\Impl\Service;

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\Transaction as TransactionModel;
use Fusio\Engine\Model\TransactionInterface;
use Fusio\Engine\Parameters;
use Fusio\Engine\Payment\PrepareContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Model\Consumer\Transaction_Prepare_Request;
use Fusio\Impl\Event\Transaction\ExecutedEvent;
use Fusio\Impl\Event\Transaction\PreparedEvent;
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
 * @link    https://www.fusio-project.org
 */
class Transaction
{
    private ConnectorInterface $connector;
    private Plan\Invoice $invoiceService;
    private ProviderFactory $providerFactory;
    private Config $config;
    private Table\Plan\Invoice $invoiceTable;
    private Table\Transaction $transactionTable;
    private EventDispatcherInterface $eventDispatcher;

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

    public function prepare(string $name, Transaction_Prepare_Request $prepare, UserContext $context): string
    {
        $provider = $this->providerFactory->factory($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $product    = $this->getProduct($prepare->getInvoiceId());
        $connection = $this->connector->getConnection($name);

        // validate return url
        $returnUrl = $prepare->getReturnUrl();
        if (empty($returnUrl) || !filter_var($returnUrl, FILTER_VALIDATE_URL)) {
            throw new StatusCode\BadRequestException('Invalid return url');
        }

        // create transaction
        $this->transactionTable->beginTransaction();

        try {
            $record = new Table\Generated\TransactionRow([
                Table\Generated\TransactionTable::COLUMN_INVOICE_ID => $prepare->getInvoiceId(),
                Table\Generated\TransactionTable::COLUMN_STATUS => TransactionModel::STATUS_CREATED,
                Table\Generated\TransactionTable::COLUMN_PROVIDER => $name,
                Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID => Uuid::pseudoRandom(),
                Table\Generated\TransactionTable::COLUMN_AMOUNT => $product->getPrice(),
                Table\Generated\TransactionTable::COLUMN_RETURN_URL => $returnUrl,
                Table\Generated\TransactionTable::COLUMN_INSERT_DATE => new \DateTime(),
            ]);

            $this->transactionTable->create($record);

            // set transaction id
            $record->setId($this->transactionTable->getLastInsertId());
            $transaction = $this->newTransactionModel($record);

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
            $this->eventDispatcher->dispatch(new PreparedEvent($transaction));

            $this->transactionTable->commit();

            return $approvalUrl;
        } catch (\Throwable $e) {
            $this->transactionTable->rollBack();

            throw $e;
        }
    }

    public function execute(string $transactionId, array $parameters): string
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
            $this->eventDispatcher->dispatch(new ExecutedEvent($transaction));

            $this->transactionTable->commit();

            return $this->buildReturnUrl($transaction);
        } catch (\Throwable $e) {
            $this->transactionTable->rollBack();

            throw $e;
        }
    }

    private function getProduct(int $invoiceId): ProductInterface
    {
        $plan = $this->invoiceTable->findPlanByInvoiceId($invoiceId);
        if (empty($plan)) {
            throw new StatusCode\BadRequestException('Invalid invoice id');
        }

        return new Product(
            $plan['id'],
            $plan['name'],
            $plan['amount'],
            $plan['points'],
            $plan['period_type']
        );
    }

    private function createTransaction(string $transactionId): TransactionModel
    {
        $result = $this->transactionTable->findOneByTransactionId($transactionId);
        if (empty($result)) {
            throw new StatusCode\BadRequestException('Invalid transaction id');
        }

        if ($result->getStatus() == TransactionInterface::STATUS_APPROVED) {
            throw new StatusCode\BadRequestException('Transaction is already approved');
        }

        return $this->newTransactionModel($result);
    }

    /**
     * Updates the status of the transaction
     */
    private function updateTransaction(TransactionInterface $transaction)
    {
        // update transaction
        $record = new Table\Generated\TransactionRow([
            Table\Generated\TransactionTable::COLUMN_ID => $transaction->getId(),
            Table\Generated\TransactionTable::COLUMN_STATUS => $transaction->getStatus(),
            Table\Generated\TransactionTable::COLUMN_REMOTE_ID => $transaction->getRemoteId(),
            Table\Generated\TransactionTable::COLUMN_UPDATE_DATE => new \DateTime(),
        ]);

        $this->transactionTable->update($record);

        // if approved add points to user
        if ($transaction->getStatus() == TransactionInterface::STATUS_APPROVED) {
            $this->invoiceService->pay($transaction);
        }
    }

    private function buildPrepareContext(TransactionInterface $transaction): PrepareContext
    {
        $baseUrl = $this->config->get('psx_url') . '/' . $this->config->get('psx_dispatch');

        return new PrepareContext(
            $baseUrl . 'consumer/transaction/execute/' . $transaction->getTransactionId(),
            $baseUrl . 'consumer/transaction/execute/' . $transaction->getTransactionId(),
            $this->config->get('fusio_payment_currency')
        );
    }

    private function buildReturnUrl(TransactionInterface $transaction): string
    {
        $returnUrl = $transaction->getReturnUrl();
        $returnUrl = str_replace('{transaction_id}', (string) $transaction->getId(), $returnUrl);

        return $returnUrl;
    }

    private function newTransactionModel(Table\Generated\TransactionRow $row): TransactionModel
    {
        return new TransactionModel(
            $row->getId(),
            $row->getInvoiceId(),
            $row->getStatus(),
            $row->getProvider(),
            $row->getTransactionId(),
            $row->getRemoteId(),
            $row->getAmount(),
            $row->getReturnUrl(),
            $row->getUpdateDate(),
            $row->getInsertDate()
        );
    }
}
