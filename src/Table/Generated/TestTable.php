<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\TestRow>
 */
class TestTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_test';
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_OPERATION_ID = 'operation_id';
    public const COLUMN_TENANT_ID = 'tenant_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_MESSAGE = 'message';
    public const COLUMN_RESPONSE = 'response';
    public const COLUMN_URI_FRAGMENTS = 'uri_fragments';
    public const COLUMN_PARAMETERS = 'parameters';
    public const COLUMN_HEADERS = 'headers';
    public const COLUMN_BODY = 'body';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_CATEGORY_ID => 0x20000a, self::COLUMN_OPERATION_ID => 0x4020000a, self::COLUMN_TENANT_ID => 0x40a00040, self::COLUMN_STATUS => 0x20000a, self::COLUMN_MESSAGE => 0x40b00000, self::COLUMN_RESPONSE => 0x40b00000, self::COLUMN_URI_FRAGMENTS => 0x40a00200, self::COLUMN_PARAMETERS => 0x40a00200, self::COLUMN_HEADERS => 0x40a00200, self::COLUMN_BODY => 0x40b00000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCategoryId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCategoryId(int $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCategoryId(int $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByCategoryId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
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
    public function findOneByOperationId(int $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByOperationId(int $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTenantId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTenantId(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTenantId(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByTenantId(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByStatus(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMessage(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMessage(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMessage(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMessage(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByResponse(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('response', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByResponse(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('response', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByResponse(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('response', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByResponse(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('response', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUriFragments(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('uri_fragments', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUriFragments(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('uri_fragments', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUriFragments(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('uri_fragments', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByUriFragments(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('uri_fragments', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByParameters(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('parameters', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('parameters', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByParameters(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('parameters', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByParameters(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('parameters', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHeaders(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('headers', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHeaders(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('headers', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByHeaders(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('headers', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByHeaders(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('headers', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\TestRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByBody(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByBody(string $value) : ?\Fusio\Impl\Table\Generated\TestRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByBody(string $value, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByBody(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\TestRow $record) : int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\TestRow $record) : int
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
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\TestRow
    {
        return \Fusio\Impl\Table\Generated\TestRow::from($row);
    }
}