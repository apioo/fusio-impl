<?php

namespace Fusio\Impl\Table\Generated;

class ConnectionRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $class = null;
    private ?string $config = null;
    private ?string $metadata = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setClass(string $class) : void
    {
        $this->class = $class;
    }
    public function getClass() : string
    {
        return $this->class ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "class" was provided');
    }
    public function setConfig(?string $config) : void
    {
        $this->config = $config;
    }
    public function getConfig() : ?string
    {
        return $this->config;
    }
    public function setMetadata(?string $metadata) : void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata() : ?string
    {
        return $this->metadata;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('class', $this->class);
        $record->put('config', $this->config);
        $record->put('metadata', $this->metadata);
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
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->class = isset($data['class']) && is_string($data['class']) ? $data['class'] : null;
        $row->config = isset($data['config']) && is_string($data['config']) ? $data['config'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}