<?php

namespace Fusio\Impl\Table\Generated;

class RateRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
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
    public function setPriority(?int $priority) : void
    {
        $this->setProperty('priority', $priority);
    }
    public function getPriority() : ?int
    {
        return $this->getProperty('priority');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
    }
    public function setRateLimit(?int $rateLimit) : void
    {
        $this->setProperty('rate_limit', $rateLimit);
    }
    public function getRateLimit() : ?int
    {
        return $this->getProperty('rate_limit');
    }
    public function setTimespan(?string $timespan) : void
    {
        $this->setProperty('timespan', $timespan);
    }
    public function getTimespan() : ?string
    {
        return $this->getProperty('timespan');
    }
}