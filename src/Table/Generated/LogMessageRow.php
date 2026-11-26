<?php

namespace Fusio\Impl\Table\Generated;

class LogMessageRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $logId = null;
    private ?int $level = null;
    private ?string $message = null;
    private ?string $context = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setLogId(int $logId): void
    {
        $this->logId = $logId;
    }
    public function getLogId(): int
    {
        return $this->logId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "log_id" was provided');
    }
    public function setLevel(int $level): void
    {
        $this->level = $level;
    }
    public function getLevel(): int
    {
        return $this->level ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "level" was provided');
    }
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
    public function getMessage(): string
    {
        return $this->message ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "message" was provided');
    }
    public function setContext(string $context): void
    {
        $this->context = $context;
    }
    public function getContext(): string
    {
        return $this->context ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "context" was provided');
    }
    public function setInsertDate(?\PSX\DateTime\LocalDateTime $insertDate): void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->insertDate;
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('log_id', $this->logId);
        $record->put('level', $this->level);
        $record->put('message', $this->message);
        $record->put('context', $this->context);
        $record->put('insert_date', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    /**
     * @param array<string, mixed>|\ArrayAccess<string, mixed> $data
     */
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->logId = isset($data['log_id']) && is_int($data['log_id']) ? $data['log_id'] : null;
        $row->level = isset($data['level']) && is_int($data['level']) ? $data['level'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->context = isset($data['context']) && is_string($data['context']) ? $data['context'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}