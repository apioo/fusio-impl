<?php

namespace Fusio\Impl\Table\Generated;

class ActionRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?string $tenantId = null;
    private ?int $taxonomyId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $class = null;
    private ?bool $async = null;
    private ?string $config = null;
    private ?string $metadata = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
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
    public function setTenantId(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }
    public function getTenantId(): ?string
    {
        return $this->tenantId;
    }
    public function setTaxonomyId(?int $taxonomyId): void
    {
        $this->taxonomyId = $taxonomyId;
    }
    public function getTaxonomyId(): ?int
    {
        return $this->taxonomyId;
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
    public function setClass(string $class): void
    {
        $this->class = $class;
    }
    public function getClass(): string
    {
        return $this->class ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "class" was provided');
    }
    public function setAsync(bool $async): void
    {
        $this->async = $async;
    }
    public function getAsync(): bool
    {
        return $this->async ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "async" was provided');
    }
    public function setConfig(?string $config): void
    {
        $this->config = $config;
    }
    public function getConfig(): ?string
    {
        return $this->config;
    }
    public function setMetadata(?string $metadata): void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata(): ?string
    {
        return $this->metadata;
    }
    public function setDate(\PSX\DateTime\LocalDateTime $date): void
    {
        $this->date = $date;
    }
    public function getDate(): \PSX\DateTime\LocalDateTime
    {
        return $this->date ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "date" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('category_id', $this->categoryId);
        $record->put('tenant_id', $this->tenantId);
        $record->put('taxonomy_id', $this->taxonomyId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('class', $this->class);
        $record->put('async', $this->async);
        $record->put('config', $this->config);
        $record->put('metadata', $this->metadata);
        $record->put('date', $this->date);
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
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->taxonomyId = isset($data['taxonomy_id']) && is_int($data['taxonomy_id']) ? $data['taxonomy_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->class = isset($data['class']) && is_string($data['class']) ? $data['class'] : null;
        $row->async = isset($data['async']) && is_bool($data['async']) ? $data['async'] : null;
        $row->config = isset($data['config']) && is_string($data['config']) ? $data['config'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}