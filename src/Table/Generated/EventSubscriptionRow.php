<?php

namespace Fusio\Impl\Table\Generated;

class EventSubscriptionRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setEventId(?int $eventId) : void
    {
        $this->setProperty('event_id', $eventId);
    }
    public function getEventId() : ?int
    {
        return $this->getProperty('event_id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setEndpoint(?string $endpoint) : void
    {
        $this->setProperty('endpoint', $endpoint);
    }
    public function getEndpoint() : ?string
    {
        return $this->getProperty('endpoint');
    }
}