<?php

namespace Fusio\Impl\Table\Generated;

class ConfigRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $tenantId = null;
    private ?int $type = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $value = null;
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
    public function setValue(string $value): void
    {
        $this->value = $value;
    }
    public function getValue(): string
    {
        return $this->value ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "value" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('tenant_id', $this->tenantId);
        $record->put('type', $this->type);
        $record->put('name', $this->name);
        $record->put('description', $this->description);
        $record->put('value', $this->value);
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
        $row->type = isset($data['type']) && is_int($data['type']) ? $data['type'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->value = isset($data['value']) && is_string($data['value']) ? $data['value'] : null;
        return $row;
    }
}