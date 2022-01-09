<?php

namespace Fusio\Impl\Table\Generated;

class UserGrantRow extends \PSX\Record\Record
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
    public function setAppId(?int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : ?int
    {
        return $this->getProperty('app_id');
    }
    public function setAllow(?int $allow) : void
    {
        $this->setProperty('allow', $allow);
    }
    public function getAllow() : ?int
    {
        return $this->getProperty('allow');
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