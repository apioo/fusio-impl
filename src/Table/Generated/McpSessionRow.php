<?php

namespace Fusio\Impl\Table\Generated;

class McpSessionRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $tenantId = null;
    private ?string $sessionId = null;
    private ?string $data = null;
    private ?\PSX\DateTime\LocalDateTime $updateDate = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }
    public function getSessionId(): string
    {
        return $this->sessionId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "session_id" was provided');
    }
    public function setData(string $data): void
    {
        $this->data = $data;
    }
    public function getData(): string
    {
        return $this->data ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "data" was provided');
    }
    public function setUpdateDate(?\PSX\DateTime\LocalDateTime $updateDate): void
    {
        $this->updateDate = $updateDate;
    }
    public function getUpdateDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->updateDate;
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
        $record->put('tenant_id', $this->tenantId);
        $record->put('session_id', $this->sessionId);
        $record->put('data', $this->data);
        $record->put('update_date', $this->updateDate);
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
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->sessionId = isset($data['session_id']) && is_string($data['session_id']) ? $data['session_id'] : null;
        $row->data = isset($data['data']) && is_string($data['data']) ? $data['data'] : null;
        $row->updateDate = isset($data['update_date']) && $data['update_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['update_date']) : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}