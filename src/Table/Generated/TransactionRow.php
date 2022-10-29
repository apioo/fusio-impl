<?php

namespace Fusio\Impl\Table\Generated;

class TransactionRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setUserId(int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : int
    {
        return $this->getProperty('user_id');
    }
    public function setPlanId(int $planId) : void
    {
        $this->setProperty('plan_id', $planId);
    }
    public function getPlanId() : int
    {
        return $this->getProperty('plan_id');
    }
    public function setTransactionId(string $transactionId) : void
    {
        $this->setProperty('transaction_id', $transactionId);
    }
    public function getTransactionId() : string
    {
        return $this->getProperty('transaction_id');
    }
    public function setAmount(int $amount) : void
    {
        $this->setProperty('amount', $amount);
    }
    public function getAmount() : int
    {
        return $this->getProperty('amount');
    }
    public function setPoints(int $points) : void
    {
        $this->setProperty('points', $points);
    }
    public function getPoints() : int
    {
        return $this->getProperty('points');
    }
    public function setPeriodStart(?\DateTime $periodStart) : void
    {
        $this->setProperty('period_start', $periodStart);
    }
    public function getPeriodStart() : ?\DateTime
    {
        return $this->getProperty('period_start');
    }
    public function setPeriodEnd(?\DateTime $periodEnd) : void
    {
        $this->setProperty('period_end', $periodEnd);
    }
    public function getPeriodEnd() : ?\DateTime
    {
        return $this->getProperty('period_end');
    }
    public function setInsertDate(\DateTime $insertDate) : void
    {
        $this->setProperty('insert_date', $insertDate);
    }
    public function getInsertDate() : \DateTime
    {
        return $this->getProperty('insert_date');
    }
}