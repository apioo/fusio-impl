<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\AgentTaskRow>
 */
class AgentTaskTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_agent_task';
    public const COLUMN_ID = 'id';
    public const COLUMN_AGENT_ID = 'agent_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_TENANT_ID = 'tenant_id';
    public const COLUMN_CONNECTION_ID = 'connection_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_ITEM = 'item';
    public const COLUMN_STEPS = 'steps';
    public const COLUMN_MESSAGE = 'message';
    public const COLUMN_START_DATE = 'start_date';
    public const COLUMN_END_DATE = 'end_date';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_AGENT_ID => 0x20000a, self::COLUMN_USER_ID => 0x20000a, self::COLUMN_TENANT_ID => 0x40a00040, self::COLUMN_CONNECTION_ID => 0x4020000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_ITEM => 0xb00000, self::COLUMN_STEPS => 0x40b00000, self::COLUMN_MESSAGE => 0x40b00000, self::COLUMN_START_DATE => 0x40800000, self::COLUMN_END_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAgentId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAgentId(int $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('agent_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAgentId(int $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserId(int $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTenantId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTenantId(string $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTenantId(string $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByTenantId(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByConnectionId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('connection_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByConnectionId(int $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('connection_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByConnectionId(int $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('connection_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByConnectionId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('connection_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByStatus(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByItem(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('item', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByItem(string $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('item', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByItem(string $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('item', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByItem(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('item', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBySteps(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('steps', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBySteps(string $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('steps', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBySteps(string $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('steps', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBySteps(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('steps', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMessage(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMessage(string $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMessage(string $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMessage(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('message', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStartDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('start_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStartDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('start_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStartDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('start_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByStartDate(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('start_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByEndDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('end_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByEndDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('end_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByEndDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('end_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByEndDate(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('end_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AgentTaskRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\AgentTaskColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\AgentTaskRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByInsertDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
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
    public function create(\Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\AgentTaskRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\AgentTaskRow $record): int
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
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\AgentTaskRow
    {
        return \Fusio\Impl\Table\Generated\AgentTaskRow::from($row);
    }
}