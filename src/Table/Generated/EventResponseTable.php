<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\EventResponseRow>
 */
class EventResponseTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_event_response';
    public const COLUMN_ID = 'id';
    public const COLUMN_TRIGGER_ID = 'trigger_id';
    public const COLUMN_SUBSCRIPTION_ID = 'subscription_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_CODE = 'code';
    public const COLUMN_ERROR = 'error';
    public const COLUMN_ATTEMPTS = 'attempts';
    public const COLUMN_EXECUTE_DATE = 'execute_date';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_TRIGGER_ID => 0x20000a, self::COLUMN_SUBSCRIPTION_ID => 0x20000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_CODE => 0x4020000a, self::COLUMN_ERROR => 0x40a000ff, self::COLUMN_ATTEMPTS => 0x20000a, self::COLUMN_EXECUTE_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTriggerId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('trigger_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTriggerId(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('trigger_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTriggerId(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('trigger_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByTriggerId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('trigger_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBySubscriptionId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('subscription_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBySubscriptionId(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('subscription_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBySubscriptionId(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('subscription_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteBySubscriptionId(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('subscription_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
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
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
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
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCode(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('code', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCode(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('code', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCode(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('code', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByCode(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('code', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByError(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('error', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByError(string $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('error', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByError(string $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('error', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByError(string $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('error', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAttempts(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('attempts', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAttempts(int $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('attempts', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAttempts(int $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('attempts', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAttempts(int $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('attempts', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExecuteDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execute_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExecuteDate(\PSX\DateTime\LocalDateTime $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execute_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByExecuteDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execute_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByExecuteDate(\PSX\DateTime\LocalDateTime $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execute_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\EventResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null) : array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\PSX\DateTime\LocalDateTime $value) : ?\Fusio\Impl\Table\Generated\EventResponseRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByInsertDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByInsertDate(\PSX\DateTime\LocalDateTime $value) : int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('insert_date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\EventResponseRow $record) : int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\EventResponseRow $record) : int
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
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\EventResponseRow
    {
        return \Fusio\Impl\Table\Generated\EventResponseRow::from($row);
    }
}