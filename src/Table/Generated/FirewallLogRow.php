<?php

namespace Fusio\Impl\Table\Generated;

class FirewallLogRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?string $tenantId = null;
    private ?string $ip = null;
    private ?int $responseCode = null;
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
    public function setIp(string $ip): void
    {
        $this->ip = $ip;
    }
    public function getIp(): string
    {
        return $this->ip ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ip" was provided');
    }
    public function setResponseCode(int $responseCode): void
    {
        $this->responseCode = $responseCode;
    }
    public function getResponseCode(): int
    {
        return $this->responseCode ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "response_code" was provided');
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
        $record->put('ip', $this->ip);
        $record->put('response_code', $this->responseCode);
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
        $row->ip = isset($data['ip']) && is_string($data['ip']) ? $data['ip'] : null;
        $row->responseCode = isset($data['response_code']) && is_int($data['response_code']) ? $data['response_code'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}