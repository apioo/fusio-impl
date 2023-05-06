<?php

namespace Fusio\Impl\Table\Generated;

class RoutesMethodRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $routeId = null;
    private ?string $method = null;
    private ?int $version = null;
    private ?int $status = null;
    private ?int $active = null;
    private ?int $public = null;
    private ?string $operationId = null;
    private ?string $description = null;
    private ?string $parameters = null;
    private ?string $request = null;
    private ?string $action = null;
    private ?int $costs = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setRouteId(int $routeId) : void
    {
        $this->routeId = $routeId;
    }
    public function getRouteId() : int
    {
        return $this->routeId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "route_id" was provided');
    }
    public function setMethod(string $method) : void
    {
        $this->method = $method;
    }
    public function getMethod() : string
    {
        return $this->method ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "method" was provided');
    }
    public function setVersion(int $version) : void
    {
        $this->version = $version;
    }
    public function getVersion() : int
    {
        return $this->version ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "version" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setActive(int $active) : void
    {
        $this->active = $active;
    }
    public function getActive() : int
    {
        return $this->active ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "active" was provided');
    }
    public function setPublic(int $public) : void
    {
        $this->public = $public;
    }
    public function getPublic() : int
    {
        return $this->public ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "public" was provided');
    }
    public function setOperationId(?string $operationId) : void
    {
        $this->operationId = $operationId;
    }
    public function getOperationId() : ?string
    {
        return $this->operationId;
    }
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    public function getDescription() : ?string
    {
        return $this->description;
    }
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    public function setRequest(?string $request) : void
    {
        $this->request = $request;
    }
    public function getRequest() : ?string
    {
        return $this->request;
    }
    public function setAction(?string $action) : void
    {
        $this->action = $action;
    }
    public function getAction() : ?string
    {
        return $this->action;
    }
    public function setCosts(?int $costs) : void
    {
        $this->costs = $costs;
    }
    public function getCosts() : ?int
    {
        return $this->costs;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('route_id', $this->routeId);
        $record->put('method', $this->method);
        $record->put('version', $this->version);
        $record->put('status', $this->status);
        $record->put('active', $this->active);
        $record->put('public', $this->public);
        $record->put('operation_id', $this->operationId);
        $record->put('description', $this->description);
        $record->put('parameters', $this->parameters);
        $record->put('request', $this->request);
        $record->put('action', $this->action);
        $record->put('costs', $this->costs);
        return $record;
    }
    public function jsonSerialize() : object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data) : self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->routeId = isset($data['route_id']) && is_int($data['route_id']) ? $data['route_id'] : null;
        $row->method = isset($data['method']) && is_string($data['method']) ? $data['method'] : null;
        $row->version = isset($data['version']) && is_int($data['version']) ? $data['version'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->active = isset($data['active']) && is_int($data['active']) ? $data['active'] : null;
        $row->public = isset($data['public']) && is_int($data['public']) ? $data['public'] : null;
        $row->operationId = isset($data['operation_id']) && is_string($data['operation_id']) ? $data['operation_id'] : null;
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->parameters = isset($data['parameters']) && is_string($data['parameters']) ? $data['parameters'] : null;
        $row->request = isset($data['request']) && is_string($data['request']) ? $data['request'] : null;
        $row->action = isset($data['action']) && is_string($data['action']) ? $data['action'] : null;
        $row->costs = isset($data['costs']) && is_int($data['costs']) ? $data['costs'] : null;
        return $row;
    }
}