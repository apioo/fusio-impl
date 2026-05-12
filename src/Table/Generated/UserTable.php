<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\UserRow>
 */
class UserTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_user';
    public const COLUMN_ID = 'id';
    public const COLUMN_IDENTITY_ID = 'identity_id';
    public const COLUMN_TENANT_ID = 'tenant_id';
    public const COLUMN_ROLE_ID = 'role_id';
    public const COLUMN_PLAN_ID = 'plan_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_REMOTE_ID = 'remote_id';
    public const COLUMN_EXTERNAL_ID = 'external_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_EMAIL = 'email';
    public const COLUMN_PASSWORD = 'password';
    public const COLUMN_POINTS = 'points';
    public const COLUMN_TOKEN = 'token';
    public const COLUMN_METADATA = 'metadata';
    public const COLUMN_DATE = 'date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_IDENTITY_ID => 0x4020000a, self::COLUMN_TENANT_ID => 0x40a00040, self::COLUMN_ROLE_ID => 0x20000a, self::COLUMN_PLAN_ID => 0x4020000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_REMOTE_ID => 0x40a000ff, self::COLUMN_EXTERNAL_ID => 0x40a000ff, self::COLUMN_NAME => 0xa00040, self::COLUMN_EMAIL => 0x40a00080, self::COLUMN_PASSWORD => 0x40a000ff, self::COLUMN_POINTS => 0x4020000a, self::COLUMN_TOKEN => 0x40a000ff, self::COLUMN_METADATA => 0x40b00000, self::COLUMN_DATE => 0x800000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\UserRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIdentityId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('identity_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIdentityId(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('identity_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByIdentityId(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('identity_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByIdentityId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('identity_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTenantId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTenantId(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTenantId(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRoleId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRoleId(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByRoleId(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByRoleId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('role_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPlanId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('plan_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPlanId(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('plan_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPlanId(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('plan_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPlanId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('plan_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByStatus(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRemoteId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('remote_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRemoteId(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('remote_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByRemoteId(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('remote_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByRemoteId(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('remote_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExternalId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExternalId(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('external_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByExternalId(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByName(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByEmail(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByEmail(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByEmail(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByEmail(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('email', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPassword(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('password', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPassword(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('password', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPassword(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('password', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPassword(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('password', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPoints(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPoints(int $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('points', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPoints(int $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByToken(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByToken(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByToken(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByToken(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('token', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMetadata(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMetadata(string $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('metadata', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMetadata(string $value, \Fusio\Impl\Table\Generated\UserRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\UserRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\UserColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\UserRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByDate(\PSX\DateTime\LocalDateTime $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\UserRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\UserRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\UserRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\UserRow $record): int
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
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\UserRow
    {
        return \Fusio\Impl\Table\Generated\UserRow::from($row);
    }
}