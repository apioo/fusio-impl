<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\RoleScopeRow>
 */
class RoleScopeTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_role_scope';
    public const COLUMN_ID = 'id';
    public const COLUMN_ROLE_ID = 'role_id';
    public const COLUMN_SCOPE_ID = 'scope_id';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_ROLE_ID => 0x200000, self::COLUMN_SCOPE_ID => 0x200000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoleScopeRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RoleScopeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoleScopeRow[]
     */
    public function findByRoleId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('role_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRoleId(int $value) : ?\Fusio\Impl\Table\Generated\RoleScopeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('role_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoleScopeRow[]
     */
    public function findByScopeId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('scope_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByScopeId(int $value) : ?\Fusio\Impl\Table\Generated\RoleScopeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('scope_id', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\RoleScopeRow';
    }
}