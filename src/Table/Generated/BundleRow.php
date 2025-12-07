<?php

namespace Fusio\Impl\Table\Generated;

class BundleRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $version = null;
    private ?string $icon = null;
    private ?string $summary = null;
    private ?string $description = null;
    private ?int $cost = null;
    private ?string $config = null;
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
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }
    public function getVersion(): string
    {
        return $this->version ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "version" was provided');
    }
    public function setIcon(string $icon): void
    {
        $this->icon = $icon;
    }
    public function getIcon(): string
    {
        return $this->icon ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "icon" was provided');
    }
    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }
    public function getSummary(): string
    {
        return $this->summary ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "summary" was provided');
    }
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
    public function getDescription(): string
    {
        return $this->description ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "description" was provided');
    }
    public function setCost(int $cost): void
    {
        $this->cost = $cost;
    }
    public function getCost(): int
    {
        return $this->cost ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "cost" was provided');
    }
    public function setConfig(string $config): void
    {
        $this->config = $config;
    }
    public function getConfig(): string
    {
        return $this->config ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "config" was provided');
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
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('version', $this->version);
        $record->put('icon', $this->icon);
        $record->put('summary', $this->summary);
        $record->put('description', $this->description);
        $record->put('cost', $this->cost);
        $record->put('config', $this->config);
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
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->version = isset($data['version']) && is_string($data['version']) ? $data['version'] : null;
        $row->icon = isset($data['icon']) && is_string($data['icon']) ? $data['icon'] : null;
        $row->summary = isset($data['summary']) && is_string($data['summary']) ? $data['summary'] : null;
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->cost = isset($data['cost']) && is_int($data['cost']) ? $data['cost'] : null;
        $row->config = isset($data['config']) && is_string($data['config']) ? $data['config'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}