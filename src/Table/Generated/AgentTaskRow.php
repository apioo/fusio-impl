<?php

namespace Fusio\Impl\Table\Generated;

class AgentTaskRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $agentId = null;
    private ?int $userId = null;
    private ?int $connectionId = null;
    private ?int $status = null;
    private ?string $item = null;
    private ?string $steps = null;
    private ?string $message = null;
    private ?\PSX\DateTime\LocalDateTime $startDate = null;
    private ?\PSX\DateTime\LocalDateTime $endDate = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setAgentId(int $agentId): void
    {
        $this->agentId = $agentId;
    }
    public function getAgentId(): int
    {
        return $this->agentId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "agent_id" was provided');
    }
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }
    public function getUserId(): int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setConnectionId(?int $connectionId): void
    {
        $this->connectionId = $connectionId;
    }
    public function getConnectionId(): ?int
    {
        return $this->connectionId;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
    public function getStatus(): int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setItem(string $item): void
    {
        $this->item = $item;
    }
    public function getItem(): string
    {
        return $this->item ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "item" was provided');
    }
    public function setSteps(?string $steps): void
    {
        $this->steps = $steps;
    }
    public function getSteps(): ?string
    {
        return $this->steps;
    }
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }
    public function getMessage(): ?string
    {
        return $this->message;
    }
    public function setStartDate(?\PSX\DateTime\LocalDateTime $startDate): void
    {
        $this->startDate = $startDate;
    }
    public function getStartDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->startDate;
    }
    public function setEndDate(?\PSX\DateTime\LocalDateTime $endDate): void
    {
        $this->endDate = $endDate;
    }
    public function getEndDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->endDate;
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate): void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate(): \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('agent_id', $this->agentId);
        $record->put('user_id', $this->userId);
        $record->put('connection_id', $this->connectionId);
        $record->put('status', $this->status);
        $record->put('item', $this->item);
        $record->put('steps', $this->steps);
        $record->put('message', $this->message);
        $record->put('start_date', $this->startDate);
        $record->put('end_date', $this->endDate);
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
        $row->agentId = isset($data['agent_id']) && is_int($data['agent_id']) ? $data['agent_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->connectionId = isset($data['connection_id']) && is_int($data['connection_id']) ? $data['connection_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->item = isset($data['item']) && is_string($data['item']) ? $data['item'] : null;
        $row->steps = isset($data['steps']) && is_string($data['steps']) ? $data['steps'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->startDate = isset($data['start_date']) && $data['start_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['start_date']) : null;
        $row->endDate = isset($data['end_date']) && $data['end_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['end_date']) : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}