<?php

namespace Fusio\Impl\Table\Generated;

class EventTriggerRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setEventId(int $eventId) : void
    {
        $this->setProperty('event_id', $eventId);
    }
    public function getEventId() : int
    {
        return $this->getProperty('event_id');
    }
    public function setStatus(int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : int
    {
        return $this->getProperty('status');
    }
    public function setPayload(string $payload) : void
    {
        $this->setProperty('payload', $payload);
    }
    public function getPayload() : string
    {
        return $this->getProperty('payload');
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