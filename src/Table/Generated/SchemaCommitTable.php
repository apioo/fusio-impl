<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\SchemaCommitRow>
 */
class SchemaCommitTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_schema_commit';
    public const COLUMN_ID = 'id';
    public const COLUMN_SCHEMA_ID = 'schema_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_PREV_HASH = 'prev_hash';
    public const COLUMN_COMMIT_HASH = 'commit_hash';
    public const COLUMN_SOURCE = 'source';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_SCHEMA_ID => 0x20000a, self::COLUMN_USER_ID => 0x20000a, self::COLUMN_PREV_HASH => 0xa00028, self::COLUMN_COMMIT_HASH => 0xa00028, self::COLUMN_SOURCE => 0xb00000, self::COLUMN_INSERT_DATE => 0x800000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteById(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBySchemaId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('schema_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBySchemaId(int $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('schema_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBySchemaId(int $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('schema_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBySchemaId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('schema_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserId(int $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByUserId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPrevHash(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('prev_hash', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPrevHash(string $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('prev_hash', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPrevHash(string $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('prev_hash', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPrevHash(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('prev_hash', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCommitHash(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('commit_hash', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCommitHash(string $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('commit_hash', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCommitHash(string $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('commit_hash', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByCommitHash(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('commit_hash', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBySource(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('source', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBySource(string $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('source', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBySource(string $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('source', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBySource(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('source', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\SchemaCommitRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\SchemaCommitColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByInsertDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByInsertDate(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\SchemaCommitRow $record): int
    {
        return $this->doDelete($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBy(\PSX\Sql\Condition $condition): int
    {
        return $this->doDeleteBy($condition);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\SchemaCommitRow
    {
        return \Fusio\Impl\Table\Generated\SchemaCommitRow::from($row);
    }
}