<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\ActionQueueRow>
 */
class ActionQueueTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_action_queue';
    public const COLUMN_ID = 'id';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_REQUEST = 'request';
    public const COLUMN_CONTEXT = 'context';
    public const COLUMN_DATE = 'date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_ACTION => 0xa000ff, self::COLUMN_REQUEST => 0xb00000, self::COLUMN_CONTEXT => 0xb00000, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ActionQueueRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\ActionQueueRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ActionQueueRow[]
     */
    public function findByAction(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAction(string $value) : ?\Fusio\Impl\Table\Generated\ActionQueueRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ActionQueueRow[]
     */
    public function findByRequest(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRequest(string $value) : ?\Fusio\Impl\Table\Generated\ActionQueueRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ActionQueueRow[]
     */
    public function findByContext(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('context', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByContext(string $value) : ?\Fusio\Impl\Table\Generated\ActionQueueRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('context', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ActionQueueRow[]
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\ActionQueueRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\ActionQueueRow';
    }
}