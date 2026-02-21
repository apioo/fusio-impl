<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\AgentMessageRow>
 */
class AgentMessageTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_agent_message';
    public const COLUMN_ID = 'id';
    public const COLUMN_AGENT_ID = 'agent_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_PARENT_ID = 'parent_id';
    public const COLUMN_ORIGIN = 'origin';
    public const COLUMN_CONTENT = 'content';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_AGENT_ID => 0x20000a, self::COLUMN_USER_ID => 0x20000a, self::COLUMN_PARENT_ID => 0x4020000a, self::COLUMN_ORIGIN => 0x20000a, self::COLUMN_CONTENT => 0xb00000, self::COLUMN_INSERT_DATE => 0x800000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAgentId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAgentId(int $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAgentId(int $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAgentId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserId(int $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByParentId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('parent_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByParentId(int $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('parent_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByParentId(int $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('parent_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByParentId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('parent_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByOrigin(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('origin', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByOrigin(int $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('origin', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByOrigin(int $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('origin', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByOrigin(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('origin', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByContent(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('content', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByContent(string $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('content', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByContent(string $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('content', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByContent(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('content', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentMessageRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentMessageColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\AgentMessageRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByInsertDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
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
    public function create(\Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\AgentMessageRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\AgentMessageRow $record): int
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
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\AgentMessageRow
    {
        return \Fusio\Impl\Table\Generated\AgentMessageRow::from($row);
    }
}