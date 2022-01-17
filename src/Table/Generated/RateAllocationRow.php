<?php

namespace Fusio\Impl\Table\Generated;

class RateAllocationRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setRateId(?int $rateId) : void
    {
        $this->setProperty('rate_id', $rateId);
    }
    public function getRateId() : ?int
    {
        return $this->getProperty('rate_id');
    }
    public function setRouteId(?int $routeId) : void
    {
        $this->setProperty('route_id', $routeId);
    }
    public function getRouteId() : ?int
    {
        return $this->getProperty('route_id');
    }
    public function setAppId(?int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : ?int
    {
        return $this->getProperty('app_id');
    }
    public function setAuthenticated(?int $authenticated) : void
    {
        $this->setProperty('authenticated', $authenticated);
    }
    public function getAuthenticated() : ?int
    {
        return $this->getProperty('authenticated');
    }
    public function setParameters(?string $parameters) : void
    {
        $this->setProperty('parameters', $parameters);
    }
    public function getParameters() : ?string
    {
        return $this->getProperty('parameters');
    }
}