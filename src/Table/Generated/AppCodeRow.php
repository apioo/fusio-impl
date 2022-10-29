<?php

namespace Fusio\Impl\Table\Generated;

class AppCodeRow extends \PSX\Record\Record
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
    public function setCode(string $code) : void
    {
        $this->setProperty('code', $code);
    }
    public function getCode() : string
    {
        return $this->getProperty('code');
    }
    public function setRedirectUri(?string $redirectUri) : void
    {
        $this->setProperty('redirect_uri', $redirectUri);
    }
    public function getRedirectUri() : ?string
    {
        return $this->getProperty('redirect_uri');
    }
    public function setScope(string $scope) : void
    {
        $this->setProperty('scope', $scope);
    }
    public function getScope() : string
    {
        return $this->getProperty('scope');
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