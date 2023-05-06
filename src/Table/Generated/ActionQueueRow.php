<?php

namespace Fusio\Impl\Table\Generated;

class ActionQueueRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $action = null;
    private ?string $request = null;
    private ?string $context = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setAction(string $action) : void
    {
        $this->action = $action;
    }
    public function getAction() : string
    {
        return $this->action ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "action" was provided');
    }
    public function setRequest(string $request) : void
    {
        $this->request = $request;
    }
    public function getRequest() : string
    {
        return $this->request ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "request" was provided');
    }
    public function setContext(string $context) : void
    {
        $this->context = $context;
    }
    public function getContext() : string
    {
        return $this->context ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "context" was provided');
    }
    public function setDate(\PSX\DateTime\LocalDateTime $date) : void
    {
        $this->date = $date;
    }
    public function getDate() : \PSX\DateTime\LocalDateTime
    {
        return $this->date ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "date" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('action', $this->action);
        $record->put('request', $this->request);
        $record->put('context', $this->context);
        $record->put('date', $this->date);
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
        $row->action = isset($data['action']) && is_string($data['action']) ? $data['action'] : null;
        $row->request = isset($data['request']) && is_string($data['request']) ? $data['request'] : null;
        $row->context = isset($data['context']) && is_string($data['context']) ? $data['context'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}