<?php

namespace Fusio\Impl\Table\Generated;

class IdentityRequestRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $identityId = null;
    private ?string $state = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setIdentityId(int $identityId) : void
    {
        $this->identityId = $identityId;
    }
    public function getIdentityId() : int
    {
        return $this->identityId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "identity_id" was provided');
    }
    public function setState(string $state) : void
    {
        $this->state = $state;
    }
    public function getState() : string
    {
        return $this->state ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "state" was provided');
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate() : \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('identity_id', $this->identityId);
        $record->put('state', $this->state);
        $record->put('insert_date', $this->insertDate);
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
        $row->identityId = isset($data['identity_id']) && is_int($data['identity_id']) ? $data['identity_id'] : null;
        $row->state = isset($data['state']) && is_string($data['state']) ? $data['state'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}