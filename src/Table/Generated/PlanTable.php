<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\PlanRow>
 */
class PlanTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_plan';
    public const COLUMN_ID = 'id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_NAME = 'name';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_POINTS = 'points';
    public const COLUMN_PERIOD_TYPE = 'period_type';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_NAME => 0xa000ff, self::COLUMN_DESCRIPTION => 0xa000ff, self::COLUMN_PRICE => 0x500000, self::COLUMN_POINTS => 0x200000, self::COLUMN_PERIOD_TYPE => 0x40200000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByDescription(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDescription(string $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByPrice(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('price', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPrice(float $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('price', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPoints(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanRow[]
     */
    public function findByPeriodType(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPeriodType(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\PlanRow';
    }
}