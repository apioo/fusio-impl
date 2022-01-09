<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\PlanInvoiceRow>
 */
class PlanInvoiceTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_plan_invoice';
    public const COLUMN_ID = 'id';
    public const COLUMN_CONTRACT_ID = 'contract_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_PREV_ID = 'prev_id';
    public const COLUMN_DISPLAY_ID = 'display_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_AMOUNT = 'amount';
    public const COLUMN_POINTS = 'points';
    public const COLUMN_FROM_DATE = 'from_date';
    public const COLUMN_TO_DATE = 'to_date';
    public const COLUMN_PAY_DATE = 'pay_date';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_CONTRACT_ID => 0x200000, self::COLUMN_USER_ID => 0x200000, self::COLUMN_PREV_ID => 0x40200000, self::COLUMN_DISPLAY_ID => 0xa000ff, self::COLUMN_STATUS => 0x200000, self::COLUMN_AMOUNT => 0x500000, self::COLUMN_POINTS => 0x200000, self::COLUMN_FROM_DATE => 0x700000, self::COLUMN_TO_DATE => 0x700000, self::COLUMN_PAY_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByContractId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('contract_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByContractId(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('contract_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByPrevId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('prev_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPrevId(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('prev_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByDisplayId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('display_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDisplayId(string $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('display_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByAmount(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAmount(float $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPoints(int $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByFromDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('from_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByFromDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('from_date', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByToDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('to_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByToDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('to_date', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByPayDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('pay_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPayDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('pay_date', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\PlanInvoiceRow[]
     */
    public function findByInsertDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByInsertDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\PlanInvoiceRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\PlanInvoiceRow';
    }
}