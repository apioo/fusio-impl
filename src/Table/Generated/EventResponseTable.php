<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\EventResponseRow>
 */
class EventResponseTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_event_response';
    public const COLUMN_ID = 'id';
    public const COLUMN_TRIGGER_ID = 'trigger_id';
    public const COLUMN_SUBSCRIPTION_ID = 'subscription_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_CODE = 'code';
    public const COLUMN_ERROR = 'error';
    public const COLUMN_ATTEMPTS = 'attempts';
    public const COLUMN_EXECUTE_DATE = 'execute_date';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_TRIGGER_ID => 0x200000, self::COLUMN_SUBSCRIPTION_ID => 0x200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_CODE => 0x40200000, self::COLUMN_ERROR => 0x40a000ff, self::COLUMN_ATTEMPTS => 0x200000, self::COLUMN_EXECUTE_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByTriggerId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('trigger_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByTriggerId(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('trigger_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findBySubscriptionId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('subscription_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneBySubscriptionId(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('subscription_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByCode(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByCode(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByError(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('error', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByError(string $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('error', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByAttempts(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('attempts', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAttempts(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('attempts', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByExecuteDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execute_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByExecuteDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execute_date', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventResponseRow[]
     */
    public function findByInsertDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByInsertDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\EventResponseRow';
    }
}