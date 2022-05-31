<?php

namespace Fusio\Impl\Table\Generated;

class PlanScopeRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setPlanId(?int $planId) : void
    {
        $this->setProperty('plan_id', $planId);
    }
    public function getPlanId() : ?int
    {
        return $this->getProperty('plan_id');
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