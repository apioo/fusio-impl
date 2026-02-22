<?php

namespace Fusio\Impl\Table\Generated;

class AgentRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $connectionId = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?int $type = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $introduction = null;
    private ?string $tools = null;
    private ?string $outgoing = null;
    private ?string $action = null;
    private ?string $metadata = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setCategoryId(int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }
    public function getCategoryId(): int
    {
        return $this->categoryId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "category_id" was provided');
    }
    public function setConnectionId(int $connectionId): void
    {
        $this->connectionId = $connectionId;
    }
    public function getConnectionId(): int
    {
        return $this->connectionId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "connection_id" was provided');
    }
    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
    public function getStatus(): int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setType(int $type): void
    {
        $this->type = $type;
    }
    public function getType(): int
    {
        return $this->type ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "type" was provided');
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function getDescription(): string
    {
        return $this->description ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "description" was provided');
    }
    public function setIntroduction(string $introduction): void
    {
        $this->introduction = $introduction;
    }
    public function getIntroduction(): string
    {
        return $this->introduction ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "introduction" was provided');
    }
    public function setTools(?string $tools): void
    {
        $this->tools = $tools;
    }
    public function getTools(): ?string
    {
        return $this->tools;
    }
    public function setOutgoing(?string $outgoing): void
    {
        $this->outgoing = $outgoing;
    }
    public function getOutgoing(): ?string
    {
        return $this->outgoing;
    }
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }
    public function getAction(): ?string
    {
        return $this->action;
    }
    public function setMetadata(?string $metadata): void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata(): ?string
    {
        return $this->metadata;
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
        $record->put('category_id', $this->categoryId);
        $record->put('connection_id', $this->connectionId);
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('type', $this->type);
        $record->put('name', $this->name);
        $record->put('description', $this->description);
        $record->put('introduction', $this->introduction);
        $record->put('tools', $this->tools);
        $record->put('outgoing', $this->outgoing);
        $record->put('action', $this->action);
        $record->put('metadata', $this->metadata);
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
        $row->categoryId = isset($data['category_id']) && is_int($data['category_id']) ? $data['category_id'] : null;
        $row->connectionId = isset($data['connection_id']) && is_int($data['connection_id']) ? $data['connection_id'] : null;
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->type = isset($data['type']) && is_int($data['type']) ? $data['type'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->introduction = isset($data['introduction']) && is_string($data['introduction']) ? $data['introduction'] : null;
        $row->tools = isset($data['tools']) && is_string($data['tools']) ? $data['tools'] : null;
        $row->outgoing = isset($data['outgoing']) && is_string($data['outgoing']) ? $data['outgoing'] : null;
        $row->action = isset($data['action']) && is_string($data['action']) ? $data['action'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}