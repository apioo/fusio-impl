<?php

namespace Fusio\Impl\Table\Generated;

class FormRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $operationId = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $uiSchema = null;
    private ?string $metadata = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setOperationId(int $operationId): void
    {
        $this->operationId = $operationId;
    }
    public function getOperationId(): int
    {
        return $this->operationId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "operation_id" was provided');
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
    public function setUiSchema(string $uiSchema): void
    {
        $this->uiSchema = $uiSchema;
    }
    public function getUiSchema(): string
    {
        return $this->uiSchema ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ui_schema" was provided');
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
        $record->put('operation_id', $this->operationId);
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('ui_schema', $this->uiSchema);
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
        $row->operationId = isset($data['operation_id']) && is_int($data['operation_id']) ? $data['operation_id'] : null;
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->uiSchema = isset($data['ui_schema']) && is_string($data['ui_schema']) ? $data['ui_schema'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}