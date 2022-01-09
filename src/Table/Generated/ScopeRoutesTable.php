<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\ScopeRoutesRow>
 */
class ScopeRoutesTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_scope_routes';
    public const COLUMN_ID = 'id';
    public const COLUMN_SCOPE_ID = 'scope_id';
    public const COLUMN_ROUTE_ID = 'route_id';
    public const COLUMN_ALLOW = 'allow';
    public const COLUMN_METHODS = 'methods';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_SCOPE_ID => 0x200000, self::COLUMN_ROUTE_ID => 0x200000, self::COLUMN_ALLOW => 0x100000, self::COLUMN_METHODS => 0x40a00040);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ScopeRoutesRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\ScopeRoutesRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ScopeRoutesRow[]
     */
    public function findByScopeId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('scope_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByScopeId(int $value) : ?\Fusio\Impl\Table\Generated\ScopeRoutesRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('scope_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ScopeRoutesRow[]
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\ScopeRoutesRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ScopeRoutesRow[]
     */
    public function findByAllow(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('allow', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAllow(int $value) : ?\Fusio\Impl\Table\Generated\ScopeRoutesRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('allow', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ScopeRoutesRow[]
     */
    public function findByMethods(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('methods', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMethods(string $value) : ?\Fusio\Impl\Table\Generated\ScopeRoutesRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('methods', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\ScopeRoutesRow';
    }
}