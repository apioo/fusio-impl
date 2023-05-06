<?php

namespace Fusio\Impl\Table\Generated;

class EventSubscriptionRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $eventId = null;
    private ?int $userId = null;
    private ?int $status = null;
    private ?string $endpoint = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setEventId(int $eventId) : void
    {
        $this->eventId = $eventId;
    }
    public function getEventId() : int
    {
        return $this->eventId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "event_id" was provided');
    }
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setEndpoint(string $endpoint) : void
    {
        $this->endpoint = $endpoint;
    }
    public function getEndpoint() : string
    {
        return $this->endpoint ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "endpoint" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('event_id', $this->eventId);
        $record->put('user_id', $this->userId);
        $record->put('status', $this->status);
        $record->put('endpoint', $this->endpoint);
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
        $row->eventId = isset($data['event_id']) && is_int($data['event_id']) ? $data['event_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->endpoint = isset($data['endpoint']) && is_string($data['endpoint']) ? $data['endpoint'] : null;
        return $row;
    }
}