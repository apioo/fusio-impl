<?php

namespace Fusio\Impl\Table\Generated;

class ScopeRoutesRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setScopeId(?int $scopeId) : void
    {
        $this->setProperty('scope_id', $scopeId);
    }
    public function getScopeId() : ?int
    {
        return $this->getProperty('scope_id');
    }
    public function setRouteId(?int $routeId) : void
    {
        $this->setProperty('route_id', $routeId);
    }
    public function getRouteId() : ?int
    {
        return $this->getProperty('route_id');
    }
    public function setAllow(?int $allow) : void
    {
        $this->setProperty('allow', $allow);
    }
    public function getAllow() : ?int
    {
        return $this->getProperty('allow');
    }
    public function setMethods(?string $methods) : void
    {
        $this->setProperty('methods', $methods);
    }
    public function getMethods() : ?string
    {
        return $this->getProperty('methods');
    }
}