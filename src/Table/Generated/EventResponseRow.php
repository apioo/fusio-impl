<?php

namespace Fusio\Impl\Table\Generated;

class EventResponseRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setTriggerId(?int $triggerId) : void
    {
        $this->setProperty('trigger_id', $triggerId);
    }
    public function getTriggerId() : ?int
    {
        return $this->getProperty('trigger_id');
    }
    public function setSubscriptionId(?int $subscriptionId) : void
    {
        $this->setProperty('subscription_id', $subscriptionId);
    }
    public function getSubscriptionId() : ?int
    {
        return $this->getProperty('subscription_id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setCode(?int $code) : void
    {
        $this->setProperty('code', $code);
    }
    public function getCode() : ?int
    {
        return $this->getProperty('code');
    }
    public function setError(?string $error) : void
    {
        $this->setProperty('error', $error);
    }
    public function getError() : ?string
    {
        return $this->getProperty('error');
    }
    public function setAttempts(?int $attempts) : void
    {
        $this->setProperty('attempts', $attempts);
    }
    public function getAttempts() : ?int
    {
        return $this->getProperty('attempts');
    }
    public function setExecuteDate(?\DateTime $executeDate) : void
    {
        $this->setProperty('execute_date', $executeDate);
    }
    public function getExecuteDate() : ?\DateTime
    {
        return $this->getProperty('execute_date');
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