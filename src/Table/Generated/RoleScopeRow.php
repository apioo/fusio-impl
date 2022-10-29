<?php

namespace Fusio\Impl\Table\Generated;

class RoleScopeRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setRoleId(int $roleId) : void
    {
        $this->setProperty('role_id', $roleId);
    }
    public function getRoleId() : int
    {
        return $this->getProperty('role_id');
    }
    public function setScopeId(int $scopeId) : void
    {
        $this->setProperty('scope_id', $scopeId);
    }
    public function getScopeId() : int
    {
        return $this->getProperty('scope_id');
    }
}