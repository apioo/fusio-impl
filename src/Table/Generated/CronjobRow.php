<?php

namespace Fusio\Impl\Table\Generated;

class CronjobRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $taxonomyId = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $cron = null;
    private ?string $action = null;
    private ?\PSX\DateTime\LocalDateTime $executeDate = null;
    private ?int $exitCode = null;
    private ?string $metadata = null;
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
    public function setTaxonomyId(?int $taxonomyId): void
    {
        $this->taxonomyId = $taxonomyId;
    }
    public function getTaxonomyId(): ?int
    {
        return $this->taxonomyId;
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
    public function setCron(string $cron): void
    {
        $this->cron = $cron;
    }
    public function getCron(): string
    {
        return $this->cron ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "cron" was provided');
    }
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }
    public function getAction(): ?string
    {
        return $this->action;
    }
    public function setExecuteDate(?\PSX\DateTime\LocalDateTime $executeDate): void
    {
        $this->executeDate = $executeDate;
    }
    public function getExecuteDate(): ?\PSX\DateTime\LocalDateTime
    {
        return $this->executeDate;
    }
    public function setExitCode(?int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }
    public function getExitCode(): ?int
    {
        return $this->exitCode;
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
        $record->put('category_id', $this->categoryId);
        $record->put('taxonomy_id', $this->taxonomyId);
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('cron', $this->cron);
        $record->put('action', $this->action);
        $record->put('execute_date', $this->executeDate);
        $record->put('exit_code', $this->exitCode);
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
        $row->categoryId = isset($data['category_id']) && is_int($data['category_id']) ? $data['category_id'] : null;
        $row->taxonomyId = isset($data['taxonomy_id']) && is_int($data['taxonomy_id']) ? $data['taxonomy_id'] : null;
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->cron = isset($data['cron']) && is_string($data['cron']) ? $data['cron'] : null;
        $row->action = isset($data['action']) && is_string($data['action']) ? $data['action'] : null;
        $row->executeDate = isset($data['execute_date']) && $data['execute_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['execute_date']) : null;
        $row->exitCode = isset($data['exit_code']) && is_int($data['exit_code']) ? $data['exit_code'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}