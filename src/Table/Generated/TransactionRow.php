<?php

namespace Fusio\Impl\Table\Generated;

class TransactionRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $planId = null;
    private ?string $transactionId = null;
    private ?int $amount = null;
    private ?int $points = null;
    private ?\PSX\DateTime\LocalDateTime $periodStart = null;
    private ?\PSX\DateTime\LocalDateTime $periodEnd = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setPlanId(int $planId) : void
    {
        $this->planId = $planId;
    }
    public function getPlanId() : int
    {
        return $this->planId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "plan_id" was provided');
    }
    public function setTransactionId(string $transactionId) : void
    {
        $this->transactionId = $transactionId;
    }
    public function getTransactionId() : string
    {
        return $this->transactionId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "transaction_id" was provided');
    }
    public function setAmount(int $amount) : void
    {
        $this->amount = $amount;
    }
    public function getAmount() : int
    {
        return $this->amount ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "amount" was provided');
    }
    public function setPoints(int $points) : void
    {
        $this->points = $points;
    }
    public function getPoints() : int
    {
        return $this->points ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "points" was provided');
    }
    public function setPeriodStart(?\PSX\DateTime\LocalDateTime $periodStart) : void
    {
        $this->periodStart = $periodStart;
    }
    public function getPeriodStart() : ?\PSX\DateTime\LocalDateTime
    {
        return $this->periodStart;
    }
    public function setPeriodEnd(?\PSX\DateTime\LocalDateTime $periodEnd) : void
    {
        $this->periodEnd = $periodEnd;
    }
    public function getPeriodEnd() : ?\PSX\DateTime\LocalDateTime
    {
        return $this->periodEnd;
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate() : \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('user_id', $this->userId);
        $record->put('plan_id', $this->planId);
        $record->put('transaction_id', $this->transactionId);
        $record->put('amount', $this->amount);
        $record->put('points', $this->points);
        $record->put('period_start', $this->periodStart);
        $record->put('period_end', $this->periodEnd);
        $record->put('insert_date', $this->insertDate);
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
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->planId = isset($data['plan_id']) && is_int($data['plan_id']) ? $data['plan_id'] : null;
        $row->transactionId = isset($data['transaction_id']) && is_string($data['transaction_id']) ? $data['transaction_id'] : null;
        $row->amount = isset($data['amount']) && is_int($data['amount']) ? $data['amount'] : null;
        $row->points = isset($data['points']) && is_int($data['points']) ? $data['points'] : null;
        $row->periodStart = isset($data['period_start']) && $data['period_start'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['period_start']) : null;
        $row->periodEnd = isset($data['period_end']) && $data['period_end'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['period_end']) : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}