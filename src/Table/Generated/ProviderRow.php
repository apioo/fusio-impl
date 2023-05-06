<?php

namespace Fusio\Impl\Table\Generated;

class ProviderRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $type = null;
    private ?string $class = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setType(string $type) : void
    {
        $this->type = $type;
    }
    public function getType() : string
    {
        return $this->type ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "type" was provided');
    }
    public function setClass(string $class) : void
    {
        $this->class = $class;
    }
    public function getClass() : string
    {
        return $this->class ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "class" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('type', $this->type);
        $record->put('class', $this->class);
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
        $row->type = isset($data['type']) && is_string($data['type']) ? $data['type'] : null;
        $row->class = isset($data['class']) && is_string($data['class']) ? $data['class'] : null;
        return $row;
    }
}