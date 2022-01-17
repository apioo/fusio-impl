<?php

namespace Fusio\Impl\Table\Generated;

class AppRow extends \PSX\Record\Record
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
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
    }
    public function setUrl(?string $url) : void
    {
        $this->setProperty('url', $url);
    }
    public function getUrl() : ?string
    {
        return $this->getProperty('url');
    }
    public function setParameters(?string $parameters) : void
    {
        $this->setProperty('parameters', $parameters);
    }
    public function getParameters() : ?string
    {
        return $this->getProperty('parameters');
    }
    public function setAppKey(?string $appKey) : void
    {
        $this->setProperty('app_key', $appKey);
    }
    public function getAppKey() : ?string
    {
        return $this->getProperty('app_key');
    }
    public function setAppSecret(?string $appSecret) : void
    {
        $this->setProperty('app_secret', $appSecret);
    }
    public function getAppSecret() : ?string
    {
        return $this->getProperty('app_secret');
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