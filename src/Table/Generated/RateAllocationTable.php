<?php

namespace Fusio\Impl\Table\Generated;

class RateAllocationTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_rate_allocation';
    public const COLUMN_ID = 'id';
    public const COLUMN_RATE_ID = 'rate_id';
    public const COLUMN_ROUTE_ID = 'route_id';
    public const COLUMN_APP_ID = 'app_id';
    public const COLUMN_AUTHENTICATED = 'authenticated';
    public const COLUMN_PARAMETERS = 'parameters';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_RATE_ID => 0x20000a, self::COLUMN_ROUTE_ID => 0x4020000a, self::COLUMN_APP_ID => 0x4020000a, self::COLUMN_AUTHENTICATED => 0x4020000a, self::COLUMN_PARAMETERS => 0x40a000ff);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRateId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('rate_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRateId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('rate_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAuthenticated(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('authenticated', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAuthenticated(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('authenticated', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByParameters(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\RateAllocationRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\RateAllocationRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\RateAllocationRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\RateAllocationRow
    {
        return new \Fusio\Impl\Table\Generated\RateAllocationRow($row);
    }
}