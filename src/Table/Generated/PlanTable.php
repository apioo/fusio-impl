<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\PlanRow>
 */
class PlanTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_plan';
    public const COLUMN_ID = 'id';
    public const COLUMN_TENANT_ID = 'tenant_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_NAME = 'name';
    public const COLUMN_DESCRIPTION = 'description';
    public const COLUMN_PRICE = 'price';
    public const COLUMN_POINTS = 'points';
    public const COLUMN_PERIOD_TYPE = 'period_type';
    public const COLUMN_EXTERNAL_ID = 'external_id';
    public const COLUMN_METADATA = 'metadata';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_TENANT_ID => 0x40a00040, self::COLUMN_STATUS => 0x20000a, self::COLUMN_NAME => 0xa000ff, self::COLUMN_DESCRIPTION => 0xa000ff, self::COLUMN_PRICE => 0x20000a, self::COLUMN_POINTS => 0x20000a, self::COLUMN_PERIOD_TYPE => 0x4020000a, self::COLUMN_EXTERNAL_ID => 0x40a000ff, self::COLUMN_METADATA => 0x40b00000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTenantId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTenantId(string $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTenantId(string $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByName(string $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByName(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDescription(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDescription(string $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByDescription(string $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByDescription(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('description', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPrice(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('price', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPrice(int $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('price', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPrice(int $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('price', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPrice(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('price', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPoints(int $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPoints(int $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPoints(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPeriodType(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('period_type', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPeriodType(int $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('period_type', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPeriodType(int $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('period_type', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPeriodType(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('period_type', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExternalId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExternalId(string $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByExternalId(string $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByExternalId(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\PlanRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMetadata(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\PlanColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMetadata(string $value): ?\Fusio\Impl\Table\Generated\PlanRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMetadata(string $value, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMetadata(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\PlanRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\PlanRow $record): int
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
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\PlanRow
    {
        return \Fusio\Impl\Table\Generated\PlanRow::from($row);
    }
}