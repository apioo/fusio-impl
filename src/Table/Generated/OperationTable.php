<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\OperationRow>
 */
class OperationTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_operation';
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ACTIVE = 'active';
    public const COLUMN_PUBLIC = 'public';
    public const COLUMN_STABILITY = 'stability';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_HTTP_METHOD = 'http_method';
    public const COLUMN_HTTP_PATH = 'http_path';
    public const COLUMN_HTTP_CODE = 'http_code';
    public const COLUMN_NAME = 'name';
    public const COLUMN_PARAMETERS = 'parameters';
    public const COLUMN_INCOMING = 'incoming';
    public const COLUMN_OUTGOING = 'outgoing';
    public const COLUMN_THROWS = 'throws';
    public const COLUMN_ACTION = 'action';
    public const COLUMN_COSTS = 'costs';
    public const COLUMN_METADATA = 'metadata';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_CATEGORY_ID => 0x20000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_ACTIVE => 0x20000a, self::COLUMN_PUBLIC => 0x20000a, self::COLUMN_STABILITY => 0x20000a, self::COLUMN_DESCRIPTION => 0x40a001f4, self::COLUMN_HTTP_METHOD => 0xa00010, self::COLUMN_HTTP_PATH => 0xa000ff, self::COLUMN_HTTP_CODE => 0x20000a, self::COLUMN_NAME => 0xa000ff, self::COLUMN_PARAMETERS => 0x40b00000, self::COLUMN_INCOMING => 0x40a000ff, self::COLUMN_OUTGOING => 0xa000ff, self::COLUMN_THROWS => 0x40b00000, self::COLUMN_ACTION => 0xa000ff, self::COLUMN_COSTS => 0x4020000a, self::COLUMN_METADATA => 0x40b00000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
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
    public function findOneByCategoryId(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCategoryId(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
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
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByActive(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('active', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByActive(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('active', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByActive(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('active', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByActive(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('active', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPublic(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('public', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPublic(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('public', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPublic(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('public', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPublic(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('public', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStability(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('stability', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStability(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('stability', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStability(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('stability', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByStability(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('stability', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDescription(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDescription(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByDescription(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByDescription(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHttpMethod(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_method', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHttpMethod(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_method', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByHttpMethod(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_method', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByHttpMethod(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_method', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHttpPath(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_path', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHttpPath(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_path', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByHttpPath(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_path', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByHttpPath(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('http_path', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHttpCode(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('http_code', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHttpCode(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('http_code', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByHttpCode(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('http_code', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByHttpCode(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('http_code', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByName(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByName(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
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
    public function findOneByParameters(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('parameters', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByParameters(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIncoming(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('incoming', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIncoming(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('incoming', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByIncoming(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('incoming', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByIncoming(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('incoming', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByOutgoing(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('outgoing', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByOutgoing(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('outgoing', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByOutgoing(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('outgoing', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByOutgoing(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('outgoing', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByThrows(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('throws', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByThrows(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('throws', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByThrows(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('throws', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByThrows(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('throws', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAction(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('action', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAction(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('action', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAction(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('action', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAction(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('action', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCosts(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('costs', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCosts(int $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('costs', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCosts(int $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('costs', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByCosts(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('costs', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\OperationRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMetadata(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMetadata(string $value) : ?\Fusio\Impl\Table\Generated\OperationRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMetadata(string $value, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMetadata(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\OperationRow $record) : int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\OperationRow $record) : int
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
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\OperationRow
    {
        return \Fusio\Impl\Table\Generated\OperationRow::from($row);
    }
}