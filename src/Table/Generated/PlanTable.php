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
    public const COLUMN_EXTERNAL_ID = 'external_id';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_NAME => 0xa000ff, self::COLUMN_DESCRIPTION => 0xa000ff, self::COLUMN_PRICE => 0x500000, self::COLUMN_POINTS => 0x20000a, self::COLUMN_PERIOD_TYPE => 0x4020000a, self::COLUMN_EXTERNAL_ID => 0x40a000ff);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDescription(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDescription(string $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPrice(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('price', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPrice(float $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('price', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPoints(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('points', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPeriodType(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPeriodType(int $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('period_type', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExternalId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('external_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExternalId(string $value) : ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('external_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\PlanRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\PlanRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\PlanRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\PlanRow
    {
        return new \Fusio\Impl\Table\Generated\PlanRow($row);
    }
}