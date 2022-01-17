<?php

namespace Fusio\Impl\Table\Generated;

class UserScopeRow extends \PSX\Record\Record
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
    public function setScopeId(?int $scopeId) : void
    {
        $this->setProperty('scope_id', $scopeId);
    }
    public function getScopeId() : ?int
    {
        return $this->getProperty('scope_id');
    }
}