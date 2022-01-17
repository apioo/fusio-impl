<?php

namespace Fusio\Impl\Table\Generated;

class AppScopeRow extends \PSX\Record\Record
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
    public function setScopeId(?int $scopeId) : void
    {
        $this->setProperty('scope_id', $scopeId);
    }
    public function getScopeId() : ?int
    {
        return $this->getProperty('scope_id');
    }
}