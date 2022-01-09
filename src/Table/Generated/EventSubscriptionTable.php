<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\EventSubscriptionRow>
 */
class EventSubscriptionTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_event_subscription';
    public const COLUMN_ID = 'id';
    public const COLUMN_EVENT_ID = 'event_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ENDPOINT = 'endpoint';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_EVENT_ID => 0x200000, self::COLUMN_USER_ID => 0x200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_ENDPOINT => 0xa000ff);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventSubscriptionRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\EventSubscriptionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventSubscriptionRow[]
     */
    public function findByEventId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('event_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByEventId(int $value) : ?\Fusio\Impl\Table\Generated\EventSubscriptionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('event_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventSubscriptionRow[]
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\EventSubscriptionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventSubscriptionRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\EventSubscriptionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\EventSubscriptionRow[]
     */
    public function findByEndpoint(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('endpoint', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByEndpoint(string $value) : ?\Fusio\Impl\Table\Generated\EventSubscriptionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('endpoint', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\EventSubscriptionRow';
    }
}