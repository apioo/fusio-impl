<?php

namespace Fusio\Impl\Table\Generated;

class ScopeOperationRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $scopeId = null;
    private ?int $operationId = null;
    private ?int $allow = null;
    private ?string $methods = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setScopeId(int $scopeId) : void
    {
        $this->scopeId = $scopeId;
    }
    public function getScopeId() : int
    {
        return $this->scopeId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "scope_id" was provided');
    }
    public function setOperationId(int $operationId) : void
    {
        $this->operationId = $operationId;
    }
    public function getOperationId() : int
    {
        return $this->operationId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "operation_id" was provided');
    }
    public function setAllow(int $allow) : void
    {
        $this->allow = $allow;
    }
    public function getAllow() : int
    {
        return $this->allow ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "allow" was provided');
    }
    public function setMethods(?string $methods) : void
    {
        $this->methods = $methods;
    }
    public function getMethods() : ?string
    {
        return $this->methods;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('scope_id', $this->scopeId);
        $record->put('operation_id', $this->operationId);
        $record->put('allow', $this->allow);
        $record->put('methods', $this->methods);
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
        $row->scopeId = isset($data['scope_id']) && is_int($data['scope_id']) ? $data['scope_id'] : null;
        $row->operationId = isset($data['operation_id']) && is_int($data['operation_id']) ? $data['operation_id'] : null;
        $row->allow = isset($data['allow']) && is_int($data['allow']) ? $data['allow'] : null;
        $row->methods = isset($data['methods']) && is_string($data['methods']) ? $data['methods'] : null;
        return $row;
    }
}