<?php

namespace Fusio\Impl\Table\Generated;

class RateRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?int $priority = null;
    private ?string $name = null;
    private ?int $rateLimit = null;
    private ?string $timespan = null;
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
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
    public function getPriority(): int
    {
        return $this->priority ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "priority" was provided');
    }
    public function setName(string $name): void
    {
        $this->name = $name;
    }
    public function getName(): string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setRateLimit(int $rateLimit): void
    {
        $this->rateLimit = $rateLimit;
    }
    public function getRateLimit(): int
    {
        return $this->rateLimit ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "rate_limit" was provided');
    }
    public function setTimespan(string $timespan): void
    {
        $this->timespan = $timespan;
    }
    public function getTimespan(): string
    {
        return $this->timespan ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "timespan" was provided');
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
        $record->put('priority', $this->priority);
        $record->put('name', $this->name);
        $record->put('rate_limit', $this->rateLimit);
        $record->put('timespan', $this->timespan);
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
        $row->priority = isset($data['priority']) && is_int($data['priority']) ? $data['priority'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->rateLimit = isset($data['rate_limit']) && is_int($data['rate_limit']) ? $data['rate_limit'] : null;
        $row->timespan = isset($data['timespan']) && is_string($data['timespan']) ? $data['timespan'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}