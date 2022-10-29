<?php

namespace Fusio\Impl\Table\Generated;

class RoutesMethodRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setRouteId(int $routeId) : void
    {
        $this->setProperty('route_id', $routeId);
    }
    public function getRouteId() : int
    {
        return $this->getProperty('route_id');
    }
    public function setMethod(string $method) : void
    {
        $this->setProperty('method', $method);
    }
    public function getMethod() : string
    {
        return $this->getProperty('method');
    }
    public function setVersion(int $version) : void
    {
        $this->setProperty('version', $version);
    }
    public function getVersion() : int
    {
        return $this->getProperty('version');
    }
    public function setStatus(int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : int
    {
        return $this->getProperty('status');
    }
    public function setActive(int $active) : void
    {
        $this->setProperty('active', $active);
    }
    public function getActive() : int
    {
        return $this->getProperty('active');
    }
    public function setPublic(int $public) : void
    {
        $this->setProperty('public', $public);
    }
    public function getPublic() : int
    {
        return $this->getProperty('public');
    }
    public function setOperationId(?string $operationId) : void
    {
        $this->setProperty('operation_id', $operationId);
    }
    public function getOperationId() : ?string
    {
        return $this->getProperty('operation_id');
    }
    public function setDescription(?string $description) : void
    {
        $this->setProperty('description', $description);
    }
    public function getDescription() : ?string
    {
        return $this->getProperty('description');
    }
    public function setParameters(?string $parameters) : void
    {
        $this->setProperty('parameters', $parameters);
    }
    public function getParameters() : ?string
    {
        return $this->getProperty('parameters');
    }
    public function setRequest(?string $request) : void
    {
        $this->setProperty('request', $request);
    }
    public function getRequest() : ?string
    {
        return $this->getProperty('request');
    }
    public function setAction(?string $action) : void
    {
        $this->setProperty('action', $action);
    }
    public function getAction() : ?string
    {
        return $this->getProperty('action');
    }
    public function setCosts(?int $costs) : void
    {
        $this->setProperty('costs', $costs);
    }
    public function getCosts() : ?int
    {
        return $this->getProperty('costs');
    }
}