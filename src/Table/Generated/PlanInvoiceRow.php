<?php

namespace Fusio\Impl\Table\Generated;

class PlanInvoiceRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setContractId(?int $contractId) : void
    {
        $this->setProperty('contract_id', $contractId);
    }
    public function getContractId() : ?int
    {
        return $this->getProperty('contract_id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setPrevId(?int $prevId) : void
    {
        $this->setProperty('prev_id', $prevId);
    }
    public function getPrevId() : ?int
    {
        return $this->getProperty('prev_id');
    }
    public function setDisplayId(?string $displayId) : void
    {
        $this->setProperty('display_id', $displayId);
    }
    public function getDisplayId() : ?string
    {
        return $this->getProperty('display_id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setAmount(?float $amount) : void
    {
        $this->setProperty('amount', $amount);
    }
    public function getAmount() : ?float
    {
        return $this->getProperty('amount');
    }
    public function setPoints(?int $points) : void
    {
        $this->setProperty('points', $points);
    }
    public function getPoints() : ?int
    {
        return $this->getProperty('points');
    }
    public function setFromDate(?\DateTime $fromDate) : void
    {
        $this->setProperty('from_date', $fromDate);
    }
    public function getFromDate() : ?\DateTime
    {
        return $this->getProperty('from_date');
    }
    public function setToDate(?\DateTime $toDate) : void
    {
        $this->setProperty('to_date', $toDate);
    }
    public function getToDate() : ?\DateTime
    {
        return $this->getProperty('to_date');
    }
    public function setPayDate(?\DateTime $payDate) : void
    {
        $this->setProperty('pay_date', $payDate);
    }
    public function getPayDate() : ?\DateTime
    {
        return $this->getProperty('pay_date');
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