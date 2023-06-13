<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\ScopeOperationRow>
 */
class ScopeOperationTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_scope_operation';
    public const COLUMN_ID = 'id';
    public const COLUMN_SCOPE_ID = 'scope_id';
    public const COLUMN_OPERATION_ID = 'operation_id';
    public const COLUMN_ALLOW = 'allow';
    public const COLUMN_METHODS = 'methods';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_SCOPE_ID => 0x20000a, self::COLUMN_OPERATION_ID => 0x20000a, self::COLUMN_ALLOW => 0x100000, self::COLUMN_METHODS => 0x40a00040);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteById(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByScopeId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('scope_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByScopeId(int $value) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('scope_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByScopeId(int $value, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('scope_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByScopeId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('scope_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByOperationId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByOperationId(int $value) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByOperationId(int $value, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByOperationId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAllow(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAllow(int $value) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAllow(int $value, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAllow(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('allow', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\ScopeOperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMethods(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('methods', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMethods(string $value) : ?\Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('methods', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMethods(string $value, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('methods', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMethods(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('methods', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\ScopeOperationRow $record) : int
    {
        return $this->doDelete($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBy(\PSX\Sql\Condition $condition) : int
    {
        return $this->doDeleteBy($condition);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\ScopeOperationRow
    {
        return \Fusio\Impl\Table\Generated\ScopeOperationRow::from($row);
    }
}