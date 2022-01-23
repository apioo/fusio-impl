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
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_ROUTE_ID => 0x20000a, self::COLUMN_METHOD => 0xa00008, self::COLUMN_VERSION => 0x20000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_ACTIVE => 0x20000a, self::COLUMN_PUBLIC => 0x20000a, self::COLUMN_OPERATION_ID => 0x40a000ff, self::COLUMN_DESCRIPTION => 0x40a001f4, self::COLUMN_PARAMETERS => 0x40a000ff, self::COLUMN_REQUEST => 0x40a000ff, self::COLUMN_ACTION => 0x40a000ff, self::COLUMN_COSTS => 0x4020000a);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMethod(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMethod(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByVersion(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('version', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByVersion(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('version', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
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
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByActive(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('active', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByActive(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('active', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPublic(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('public', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPublic(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('public', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByOperationId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('operation_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByOperationId(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('operation_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
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
    public function findOneByDescription(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('description', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByParameters(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('parameters', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRequest(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRequest(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('request', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAction(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAction(string $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('action', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesMethodRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCosts(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('costs', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCosts(int $value) : ?\Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('costs', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\RoutesMethodRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\RoutesMethodRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\RoutesMethodRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\RoutesMethodRow
    {
        return new \Fusio\Impl\Table\Generated\RoutesMethodRow($row);
    }
}