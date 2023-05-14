<?php

namespace Fusio\Impl\Table\Generated;

class RateAllocationRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $rateId = null;
    private ?int $operationId = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?int $planId = null;
    private ?int $authenticated = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setRateId(int $rateId) : void
    {
        $this->rateId = $rateId;
    }
    public function getRateId() : int
    {
        return $this->rateId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "rate_id" was provided');
    }
    public function setOperationId(?int $operationId) : void
    {
        $this->operationId = $operationId;
    }
    public function getOperationId() : ?int
    {
        return $this->operationId;
    }
    public function setAppId(?int $appId) : void
    {
        $this->appId = $appId;
    }
    public function getAppId() : ?int
    {
        return $this->appId;
    }
    public function setUserId(?int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : ?int
    {
        return $this->userId;
    }
    public function setPlanId(?int $planId) : void
    {
        $this->planId = $planId;
    }
    public function getPlanId() : ?int
    {
        return $this->planId;
    }
    public function setAuthenticated(?int $authenticated) : void
    {
        $this->authenticated = $authenticated;
    }
    public function getAuthenticated() : ?int
    {
        return $this->authenticated;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('rate_id', $this->rateId);
        $record->put('operation_id', $this->operationId);
        $record->put('app_id', $this->appId);
        $record->put('user_id', $this->userId);
        $record->put('plan_id', $this->planId);
        $record->put('authenticated', $this->authenticated);
        return $record;
    }
    public function jsonSerialize() : object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data) : self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->rateId = isset($data['rate_id']) && is_int($data['rate_id']) ? $data['rate_id'] : null;
        $row->operationId = isset($data['operation_id']) && is_int($data['operation_id']) ? $data['operation_id'] : null;
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->planId = isset($data['plan_id']) && is_int($data['plan_id']) ? $data['plan_id'] : null;
        $row->authenticated = isset($data['authenticated']) && is_int($data['authenticated']) ? $data['authenticated'] : null;
        return $row;
    }
}