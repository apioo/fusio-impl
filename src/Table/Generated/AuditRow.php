<?php

namespace Fusio\Impl\Table\Generated;

class AuditRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setAppId(?int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : ?int
    {
        return $this->getProperty('app_id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setRefId(?int $refId) : void
    {
        $this->setProperty('ref_id', $refId);
    }
    public function getRefId() : ?int
    {
        return $this->getProperty('ref_id');
    }
    public function setEvent(?string $event) : void
    {
        $this->setProperty('event', $event);
    }
    public function getEvent() : ?string
    {
        return $this->getProperty('event');
    }
    public function setIp(?string $ip) : void
    {
        $this->setProperty('ip', $ip);
    }
    public function getIp() : ?string
    {
        return $this->getProperty('ip');
    }
    public function setMessage(?string $message) : void
    {
        $this->setProperty('message', $message);
    }
    public function getMessage() : ?string
    {
        return $this->getProperty('message');
    }
    public function setContent(?string $content) : void
    {
        $this->setProperty('content', $content);
    }
    public function getContent() : ?string
    {
        return $this->getProperty('content');
    }
    public function setDate(?\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : ?\DateTime
    {
        return $this->getProperty('date');
    }
}