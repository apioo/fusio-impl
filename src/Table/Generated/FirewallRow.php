<?php

namespace Fusio\Impl\Table\Generated;

class FirewallRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?int $type = null;
    private ?string $ip = null;
    private ?\PSX\DateTime\LocalDateTime $expire = null;
    private ?string $metadata = null;
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
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }
    public function getStatus(): int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setType(int $type): void
    {
        $this->type = $type;
    }
    public function getType(): int
    {
        return $this->type ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "type" was provided');
    }
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
    public function getIp(): string
    {
        return $this->ip ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ip" was provided');
    }
    public function setExpire(?\PSX\DateTime\LocalDateTime $expire): void
    {
        $this->expire = $expire;
    }
    public function getExpire(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->expire;
    }
    public function setMetadata(?string $metadata): void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata(): ?string
    {
        return $this->metadata;
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('type', $this->type);
        $record->put('ip', $this->ip);
        $record->put('expire', $this->expire);
        $record->put('metadata', $this->metadata);
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
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->type = isset($data['type']) && is_int($data['type']) ? $data['type'] : null;
        $row->ip = isset($data['ip']) && is_string($data['ip']) ? $data['ip'] : null;
        $row->expire = isset($data['expire']) && $data['expire'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['expire']) : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}