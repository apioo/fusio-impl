<?php

namespace Fusio\Impl\Table\Generated;

class EventResponseRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $subscriptionId = null;
    private ?int $status = null;
    private ?int $attempts = null;
    private ?int $code = null;
    private ?string $body = null;
    private ?\PSX\DateTime\LocalDateTime $executeDate = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setSubscriptionId(int $subscriptionId) : void
    {
        $this->subscriptionId = $subscriptionId;
    }
    public function getSubscriptionId() : int
    {
        return $this->subscriptionId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "subscription_id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setAttempts(int $attempts) : void
    {
        $this->attempts = $attempts;
    }
    public function getAttempts() : int
    {
        return $this->attempts ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "attempts" was provided');
    }
    public function setCode(?int $code) : void
    {
        $this->code = $code;
    }
    public function getCode() : ?int
    {
        return $this->code;
    }
    public function setBody(?string $body) : void
    {
        $this->body = $body;
    }
    public function getBody() : ?string
    {
        return $this->body;
    }
    public function setExecuteDate(?\PSX\DateTime\LocalDateTime $executeDate) : void
    {
        $this->executeDate = $executeDate;
    }
    public function getExecuteDate() : ?\PSX\DateTime\LocalDateTime
    {
        return $this->executeDate;
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
        $record->put('subscription_id', $this->subscriptionId);
        $record->put('status', $this->status);
        $record->put('attempts', $this->attempts);
        $record->put('code', $this->code);
        $record->put('body', $this->body);
        $record->put('execute_date', $this->executeDate);
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
        $row->subscriptionId = isset($data['subscription_id']) && is_int($data['subscription_id']) ? $data['subscription_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->attempts = isset($data['attempts']) && is_int($data['attempts']) ? $data['attempts'] : null;
        $row->code = isset($data['code']) && is_int($data['code']) ? $data['code'] : null;
        $row->body = isset($data['body']) && is_string($data['body']) ? $data['body'] : null;
        $row->executeDate = isset($data['execute_date']) && $data['execute_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['execute_date']) : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}