<?php

namespace Fusio\Impl\Table\Generated;

class PlanRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $price = null;
    private ?int $points = null;
    private ?int $periodType = null;
    private ?string $externalId = null;
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
    public function setDescription(string $description) : void
    {
        $this->description = $description;
    }
    public function getDescription() : string
    {
        return $this->description ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "description" was provided');
    }
    public function setPrice(string $price) : void
    {
        $this->price = $price;
    }
    public function getPrice() : string
    {
        return $this->price ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "price" was provided');
    }
    public function setPoints(int $points) : void
    {
        $this->points = $points;
    }
    public function getPoints() : int
    {
        return $this->points ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "points" was provided');
    }
    public function setPeriodType(?int $periodType) : void
    {
        $this->periodType = $periodType;
    }
    public function getPeriodType() : ?int
    {
        return $this->periodType;
    }
    public function setExternalId(?string $externalId) : void
    {
        $this->externalId = $externalId;
    }
    public function getExternalId() : ?string
    {
        return $this->externalId;
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
        $record->put('description', $this->description);
        $record->put('price', $this->price);
        $record->put('points', $this->points);
        $record->put('period_type', $this->periodType);
        $record->put('external_id', $this->externalId);
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
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->price = isset($data['price']) && is_string($data['price']) ? $data['price'] : null;
        $row->points = isset($data['points']) && is_int($data['points']) ? $data['points'] : null;
        $row->periodType = isset($data['period_type']) && is_int($data['period_type']) ? $data['period_type'] : null;
        $row->externalId = isset($data['external_id']) && is_string($data['external_id']) ? $data['external_id'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}