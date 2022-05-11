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

use Fusio\Engine\ConnectorInterface;
use Fusio\Engine\Model\Product;
use Fusio\Engine\Model\ProductInterface;
use Fusio\Engine\Model\Transaction as TransactionModel;
use Fusio\Engine\Model\TransactionInterface;
use Fusio\Engine\Model\UserInterface;
use Fusio\Engine\Payment\PrepareContext;
use Fusio\Engine\Payment\ProviderInterface;
use Fusio\Impl\Authorization\UserContext;
use Fusio\Impl\Provider\ProviderFactory;
use Fusio\Impl\Table;
use Fusio\Model\Consumer\Transaction_Prepare_Request;
use PSX\Framework\Config\Config;
use PSX\Http\Exception as StatusCode;
use PSX\Http\RequestInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Payment
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org
 */
class Payment
{
    private ConnectorInterface $connector;
    private ProviderFactory $providerFactory;
    private Config $config;
    private Table\Transaction $transactionTable;
    private Webhook $webhookHandler;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(ConnectorInterface $connector, ProviderFactory $providerFactory, Config $config, Table\Transaction $transactionTable, Webhook $webhookHandler, EventDispatcherInterface $eventDispatcher)
    {
        $this->connector = $connector;
        $this->providerFactory = $providerFactory;
        $this->config = $config;
        $this->transactionTable = $transactionTable;
        $this->webhookHandler = $webhookHandler;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function checkout(string $name, Transaction_Prepare_Request $prepare, UserInterface $user, UserContext $context): string
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

        return $provider->checkout(
            $connection,
            $product,
            $user,
            $this->buildPrepareContext($returnUrl)
        );
    }

    public function webhook(string $name, RequestInterface $request)
    {
        $provider = $this->providerFactory->factory($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $connection = $this->connector->getConnection($name);

        $webhookSecret = $this->configService->get('payment_' . strtolower($name) . '_secret');

        return $provider->webhook($connection, $request, $this->webhookHandler, $webhookSecret);
    }

    public function portal(string $name, UserInterface $user, string $returnUrl)
    {
        $provider = $this->providerFactory->factory($name);
        if (!$provider instanceof ProviderInterface) {
            throw new StatusCode\BadRequestException('Provider is not available');
        }

        $externalId = $user->getExternalId();
        if (empty($externalId)) {
            throw new StatusCode\BadRequestException('User ');
        }

        return $provider->portal($user, $returnUrl);
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

    private function buildPrepareContext(string $returnUrl, string $customerId): PrepareContext
    {
        return new PrepareContext(
            $returnUrl,
            $returnUrl,
            $this->config->get('fusio_payment_currency'),
            $customerId
        );
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
