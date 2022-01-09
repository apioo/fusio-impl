<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\RoutesMethodRow>
 */
class RoutesMethodTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_routes_method';
    public const COLUMN_ID = 'id';
    public const COLUMN_ROUTE_ID = 'route_id';
    public const COLUMN_METHOD = 'method';
    public const COLUMN_VERSION = 'version';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ACTIVE = 'active';
    public const COLUMN_PUBLIC = 'public';
    public const COLUMN_OPERATION_ID = 'operation_id';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_PARAMETERS = 'parameters';
    public const COLUMN_REQUEST = 'request';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_COSTS = 'costs';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_ROUTE_ID => 0x200000, self::COLUMN_METHOD => 0xa00008, self::COLUMN_VERSION => 0x200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_ACTIVE => 0x200000, self::COLUMN_PUBLIC => 0x200000, self::COLUMN_OPERATION_ID => 0x40a000ff, self::COLUMN_DESCRIPTION => 0x40a001f4, self::COLUMN_PARAMETERS => 0x40a000ff, self::COLUMN_REQUEST => 0x40a000ff, self::COLUMN_ACTION => 0x40a000ff, self::COLUMN_COSTS => 0x40200000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByMethod(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMethod(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByVersion(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('version', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByVersion(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('version', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByActive(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('active', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByActive(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('active', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByPublic(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('public', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPublic(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('public', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByOperationId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('operation_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByOperationId(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('operation_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByDescription(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDescription(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByParameters(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByRequest(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRequest(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByAction(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAction(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesMethodRow[]
     */
    public function findByCosts(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('costs', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByCosts(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('costs', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\RoutesMethodRow';
    }
}