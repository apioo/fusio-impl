<?php

namespace Fusio\Impl\Table\Generated;

class TransactionRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setInvoiceId(?int $invoiceId) : void
    {
        $this->setProperty('invoice_id', $invoiceId);
    }
    public function getInvoiceId() : ?int
    {
        return $this->getProperty('invoice_id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setProvider(?string $provider) : void
    {
        $this->setProperty('provider', $provider);
    }
    public function getProvider() : ?string
    {
        return $this->getProperty('provider');
    }
    public function setTransactionId(?string $transactionId) : void
    {
        $this->setProperty('transaction_id', $transactionId);
    }
    public function getTransactionId() : ?string
    {
        return $this->getProperty('transaction_id');
    }
    public function setRemoteId(?string $remoteId) : void
    {
        $this->setProperty('remote_id', $remoteId);
    }
    public function getRemoteId() : ?string
    {
        return $this->getProperty('remote_id');
    }
    public function setAmount(?float $amount) : void
    {
        $this->setProperty('amount', $amount);
    }
    public function getAmount() : ?float
    {
        return $this->getProperty('amount');
    }
    public function setReturnUrl(?string $returnUrl) : void
    {
        $this->setProperty('return_url', $returnUrl);
    }
    public function getReturnUrl() : ?string
    {
        return $this->getProperty('return_url');
    }
    public function setUpdateDate(?\DateTime $updateDate) : void
    {
        $this->setProperty('update_date', $updateDate);
    }
    public function getUpdateDate() : ?\DateTime
    {
        return $this->getProperty('update_date');
    }
    public function setInsertDate(?\DateTime $insertDate) : void
    {
        $this->setProperty('insert_date', $insertDate);
    }
    public function getInsertDate() : ?\DateTime
    {
        return $this->getProperty('insert_date');
    }
}