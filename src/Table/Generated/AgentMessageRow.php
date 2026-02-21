<?php

namespace Fusio\Impl\Table\Generated;

class AgentMessageRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $agentId = null;
    private ?int $userId = null;
    private ?int $parentId = null;
    private ?int $origin = null;
    private ?string $content = null;
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
    public function setParentId(?int $parentId): void
    {
        $this->parentId = $parentId;
    }
    public function getParentId(): ?int
    {
        return $this->parentId;
    }
    public function setOrigin(int $origin): void
    {
        $this->origin = $origin;
    }
    public function getOrigin(): int
    {
        return $this->origin ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "origin" was provided');
    }
    public function setContent(string $content): void
    {
        $this->content = $content;
    }
    public function getContent(): string
    {
        return $this->content ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "content" was provided');
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
        $record->put('parent_id', $this->parentId);
        $record->put('origin', $this->origin);
        $record->put('content', $this->content);
        $record->put('insert_date', $this->insertDate);
        return $record;
    }
    public function jsonSerialize(): object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data): self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->agentId = isset($data['agent_id']) && is_int($data['agent_id']) ? $data['agent_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->parentId = isset($data['parent_id']) && is_int($data['parent_id']) ? $data['parent_id'] : null;
        $row->origin = isset($data['origin']) && is_int($data['origin']) ? $data['origin'] : null;
        $row->content = isset($data['content']) && is_string($data['content']) ? $data['content'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}