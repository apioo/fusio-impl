<?php

namespace Fusio\Impl\Table\Generated;

class AppScopeRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $appId = null;
    private ?int $scopeId = null;
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setAppId(int $appId): void
    {
        $this->appId = $appId;
    }
    public function getAppId(): int
    {
        return $this->appId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_id" was provided');
    }
    public function setScopeId(int $scopeId): void
    {
        $this->scopeId = $scopeId;
    }
    public function getScopeId(): int
    {
        return $this->scopeId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "scope_id" was provided');
    }
    public function toRecord(): \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('app_id', $this->appId);
        $record->put('scope_id', $this->scopeId);
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
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->scopeId = isset($data['scope_id']) && is_int($data['scope_id']) ? $data['scope_id'] : null;
        return $row;
    }
}