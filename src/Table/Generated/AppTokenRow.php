<?php

namespace Fusio\Impl\Table\Generated;

class AppTokenRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setAppId(int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : int
    {
        return $this->getProperty('app_id');
    }
    public function setUserId(int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : int
    {
        return $this->getProperty('user_id');
    }
    public function setStatus(int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : int
    {
        return $this->getProperty('status');
    }
    public function setToken(string $token) : void
    {
        $this->setProperty('token', $token);
    }
    public function getToken() : string
    {
        return $this->getProperty('token');
    }
    public function setRefresh(?string $refresh) : void
    {
        $this->setProperty('refresh', $refresh);
    }
    public function getRefresh() : ?string
    {
        return $this->getProperty('refresh');
    }
    public function setScope(string $scope) : void
    {
        $this->setProperty('scope', $scope);
    }
    public function getScope() : string
    {
        return $this->getProperty('scope');
    }
    public function setIp(string $ip) : void
    {
        $this->setProperty('ip', $ip);
    }
    public function getIp() : string
    {
        return $this->getProperty('ip');
    }
    public function setExpire(?\DateTime $expire) : void
    {
        $this->setProperty('expire', $expire);
    }
    public function getExpire() : ?\DateTime
    {
        return $this->getProperty('expire');
    }
    public function setDate(\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : \DateTime
    {
        return $this->getProperty('date');
    }
}