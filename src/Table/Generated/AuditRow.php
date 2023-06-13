<?php

namespace Fusio\Impl\Table\Generated;

class AuditRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?int $refId = null;
    private ?string $event = null;
    private ?string $ip = null;
    private ?string $message = null;
    private ?string $content = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setAppId(int $appId) : void
    {
        $this->appId = $appId;
    }
    public function getAppId() : int
    {
        return $this->appId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_id" was provided');
    }
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setRefId(?int $refId) : void
    {
        $this->refId = $refId;
    }
    public function getRefId() : ?int
    {
        return $this->refId;
    }
    public function setEvent(string $event) : void
    {
        $this->event = $event;
    }
    public function getEvent() : string
    {
        return $this->event ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "event" was provided');
    }
    public function setIp(string $ip) : void
    {
        $this->ip = $ip;
    }
    public function getIp() : string
    {
        return $this->ip ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ip" was provided');
    }
    public function setMessage(string $message) : void
    {
        $this->message = $message;
    }
    public function getMessage() : string
    {
        return $this->message ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "message" was provided');
    }
    public function setContent(?string $content) : void
    {
        $this->content = $content;
    }
    public function getContent() : ?string
    {
        return $this->content;
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
        $record->put('app_id', $this->appId);
        $record->put('user_id', $this->userId);
        $record->put('ref_id', $this->refId);
        $record->put('event', $this->event);
        $record->put('ip', $this->ip);
        $record->put('message', $this->message);
        $record->put('content', $this->content);
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
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->refId = isset($data['ref_id']) && is_int($data['ref_id']) ? $data['ref_id'] : null;
        $row->event = isset($data['event']) && is_string($data['event']) ? $data['event'] : null;
        $row->ip = isset($data['ip']) && is_string($data['ip']) ? $data['ip'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->content = isset($data['content']) && is_string($data['content']) ? $data['content'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}