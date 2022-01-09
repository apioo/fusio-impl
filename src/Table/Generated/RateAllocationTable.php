<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\RateAllocationRow>
 */
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
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_RATE_ID => 0x200000, self::COLUMN_ROUTE_ID => 0x40200000, self::COLUMN_APP_ID => 0x40200000, self::COLUMN_AUTHENTICATED => 0x40200000, self::COLUMN_PARAMETERS => 0x40a000ff);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findByRateId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('rate_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRateId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('rate_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findByAuthenticated(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('authenticated', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAuthenticated(int $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('authenticated', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RateAllocationRow[]
     */
    public function findByParameters(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\RateAllocationRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\RateAllocationRow';
    }
}