<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\PlanContractRow>
 */
class PlanContractTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_plan_contract';
    public const COLUMN_ID = 'id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_PLAN_ID = 'plan_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_AMOUNT = 'amount';
    public const COLUMN_POINTS = 'points';
    public const COLUMN_PERIOD_TYPE = 'period_type';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_USER_ID => 0x200000, self::COLUMN_PLAN_ID => 0x200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_AMOUNT => 0x500000, self::COLUMN_POINTS => 0x200000, self::COLUMN_PERIOD_TYPE => 0x40200000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByPlanId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('plan_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPlanId(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('plan_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByAmount(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAmount(float $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPoints(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByPeriodType(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPeriodType(int $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanContractRow[]
     */
    public function findByInsertDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByInsertDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\PlanContractRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\PlanContractRow';
    }
}