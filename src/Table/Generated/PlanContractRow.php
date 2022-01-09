<?php

namespace Fusio\Impl\Table\Generated;

class PlanContractRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setPlanId(?int $planId) : void
    {
        $this->setProperty('plan_id', $planId);
    }
    public function getPlanId() : ?int
    {
        return $this->getProperty('plan_id');
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
    public function setPeriodType(?int $periodType) : void
    {
        $this->setProperty('period_type', $periodType);
    }
    public function getPeriodType() : ?int
    {
        return $this->getProperty('period_type');
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