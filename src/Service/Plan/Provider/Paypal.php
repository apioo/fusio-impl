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

namespace Fusio\Impl\Service\Plan\Provider;

use Fusio\Impl\Service\Plan\Model\Product;
use Fusio\Impl\Service\Plan\ProviderInterface;
use PayPal\Api;
use PayPal\Rest\ApiContext;
use PSX\Http\Exception as StatusCode;
use Fusio\Impl\Service\Plan\Model;

/**
 * Paypal
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Paypal implements ProviderInterface
{
    /**
     * @var string
     */
    protected $currency;

    public function __construct()
    {
        $this->currency = 'EUR';
    }

    /**
     * @inheritdoc
     */
    public function prepare($connection, Product $product)
    {
        $apiContext = $this->getApiContext($connection);

        $returnUrl = '';
        $cancelUrl = '';

        // create payment
        $payment = $this->createPayment($product, $returnUrl, $cancelUrl);
        $payment->create($apiContext);

        $transaction = $this->createTransaction($payment);
        $transaction->setParameter('approvalUrl', $payment->getApprovalLink());

        return $transaction;
    }

    /**
     * @inheritdoc
     */
    public function execute($connection, Product $product, Model\Transaction $transaction)
    {
        $apiContext = $this->getApiContext($connection);

        $paymentId = $transaction->getId();
        $success   = $transaction->getParameter('success');
        $payerId   = $transaction->getParameter('payerId');

        if ($success !== 'true') {
            throw new StatusCode\BadRequestException('Payment was not successful');
        }

        $execution = $this->createPaymentExecution($payerId, $product->getPrice());

        // execute payment
        $payment = Api\Payment::get($paymentId, $apiContext);
        $payment->execute($execution, $apiContext);

        return $this->createTransaction($payment, $transaction);
    }

    /**
     * @param mixed $connection
     * @return \PayPal\Rest\ApiContext
     */
    private function getApiContext($connection)
    {
        if ($connection instanceof ApiContext) {
            return $connection;
        } else {
            throw new StatusCode\InternalServerErrorException('Connection must return a Paypal API context');
        }
    }

    /**
     * @param \PayPal\Api\Payment $payment
     * @return float|int
     */
    private function getTotalAmount(Api\Payment $payment)
    {
        $amount = 0;
        $transactions = $payment->getTransactions();
        if (is_array($transactions)) {
            foreach ($transactions as $transaction) {
                /** @var Api\Transaction $transaction */
                $amount+= floatval($transaction->getAmount()->getTotal());
            }
        }

        return $amount;
    }

    /**
     * @param string $payerId
     * @param float $total
     * @return \PayPal\Api\PaymentExecution
     */
    private function createPaymentExecution($payerId, $total)
    {
        $amount = new Api\Amount();
        $amount->setCurrency($this->currency);
        $amount->setTotal($total);

        $transaction = new Api\Transaction();
        $transaction->setAmount($amount);

        $execution = new Api\PaymentExecution();
        $execution->setPayerId($payerId);
        $execution->addTransaction($transaction);

        return $execution;
    }

    /**
     * @param \PayPal\Api\Payment $payment
     * @param \Fusio\Impl\Service\Plan\Model\Transaction|null $transaction
     * @return \Fusio\Impl\Service\Plan\Model\Transaction
     */
    private function createTransaction(Api\Payment $payment, Model\Transaction $transaction = null)
    {
        $status = Model\Transaction::STATUS_UNKNOWN;
        if ($payment->getState() == 'created') {
            $status = Model\Transaction::STATUS_CREATED;
        } elseif ($payment->getState() == 'approved') {
            $status = Model\Transaction::STATUS_APPROVED;
        } elseif ($payment->getState() == 'failed') {
            $status = Model\Transaction::STATUS_FAILED;
        }

        $amount = $this->getTotalAmount($payment);
        if ($amount <= 0) {
            throw new StatusCode\BadRequestException('It looks like nothing was payed');
        }

        if ($transaction === null) {
            $transaction = new Model\Transaction();
        }

        $transaction->setStatus($status);
        $transaction->setTransactionId($payment->getId());
        $transaction->setAmount($amount);
        $transaction->setCreateDate(new \DateTime($payment->getCreateTime()));

        return $transaction;
    }

    /**
     * @param \Fusio\Impl\Service\Plan\Model\Product $product
     * @param string $returnUrl
     * @param string $cancelUrl
     * @return \PayPal\Api\Payment
     */
    private function createPayment(Model\Product $product, $returnUrl, $cancelUrl)
    {
        $payer = new Api\Payer();
        $payer->setPaymentMethod('paypal');

        $item = new Api\Item();
        $item->setName($product->getName())
            ->setCurrency($this->currency)
            ->setQuantity(1)
            ->setSku($product->getId())
            ->setPrice($product->getPrice());

        $itemList = new Api\ItemList();
        $itemList->setItems([$item]);

        $amount = new Api\Amount();
        $amount->setCurrency($this->currency)
            ->setTotal($product->getPrice());

        $transaction = new Api\Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setInvoiceNumber(uniqid());

        $redirectUrls = new Api\RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl)
            ->setCancelUrl($cancelUrl);

        $payment = new Api\Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        return $payment;
    }
}
