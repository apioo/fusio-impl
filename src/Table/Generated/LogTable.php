<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\LogRow>
 */
class LogTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_log';
    public const COLUMN_ID = 'id';
    public const COLUMN_TENANT_ID = 'tenant_id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_OPERATION_ID = 'operation_id';
    public const COLUMN_APP_ID = 'app_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_IP = 'ip';
    public const COLUMN_USER_AGENT = 'user_agent';
    public const COLUMN_METHOD = 'method';
    public const COLUMN_PATH = 'path';
    public const COLUMN_HEADER = 'header';
    public const COLUMN_BODY = 'body';
    public const COLUMN_RESPONSE_CODE = 'response_code';
    public const COLUMN_EXECUTION_TIME = 'execution_time';
    public const COLUMN_DATE = 'date';
    public function getName(): string
    {
        return self::NAME;
    }
    public function getColumns(): array
    {
        return [self::COLUMN_ID => 0x3020000a, self::COLUMN_TENANT_ID => 0x40a00040, self::COLUMN_CATEGORY_ID => 0x20000a, self::COLUMN_OPERATION_ID => 0x4020000a, self::COLUMN_APP_ID => 0x4020000a, self::COLUMN_USER_ID => 0x4020000a, self::COLUMN_IP => 0xa00028, self::COLUMN_USER_AGENT => 0xa000ff, self::COLUMN_METHOD => 0xa00010, self::COLUMN_PATH => 0xa003ff, self::COLUMN_HEADER => 0xb00000, self::COLUMN_BODY => 0x40b00000, self::COLUMN_RESPONSE_CODE => 0x20000a, self::COLUMN_EXECUTION_TIME => 0x4020000a, self::COLUMN_DATE => 0x800000];
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition): ?\Fusio\Impl\Table\Generated\LogRow
    {
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateById(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTenantId(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTenantId(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('tenant_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByTenantId(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCategoryId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCategoryId(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByCategoryId(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByCategoryId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('category_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByOperationId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByOperationId(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByOperationId(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByOperationId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('operation_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAppId(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByAppId(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByAppId(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('app_id', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserId(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
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
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIp(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('ip', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIp(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('ip', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByIp(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('ip', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByIp(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('ip', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserAgent(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_agent', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserAgent(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_agent', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByUserAgent(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_agent', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByUserAgent(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('user_agent', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMethod(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('method', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMethod(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('method', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByMethod(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('method', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByMethod(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('method', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPath(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('path', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPath(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('path', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByPath(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('path', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByPath(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('path', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHeader(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('header', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHeader(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('header', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByHeader(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('header', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByHeader(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('header', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByBody(string $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByBody(string $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByBody(string $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByBody(string $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->like('body', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByResponseCode(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('response_code', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByResponseCode(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('response_code', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByResponseCode(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('response_code', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByResponseCode(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('response_code', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExecutionTime(int $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execution_time', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExecutionTime(int $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execution_time', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByExecutionTime(int $value, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execution_time', $value);
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function deleteByExecutionTime(int $value): int
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('execution_time', $value);
        return $this->doDeleteBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDate(\PSX\DateTime\LocalDateTime $value, ?int $startIndex = null, ?int $count = null, ?\Fusio\Impl\Table\Generated\LogColumn $sortBy = null, ?\PSX\Sql\OrderBy $sortOrder = null): array
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDate(\PSX\DateTime\LocalDateTime $value): ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = \PSX\Sql\Condition::withAnd();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateByDate(\PSX\DateTime\LocalDateTime $value, \Fusio\Impl\Table\Generated\LogRow $record): int
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
    public function create(\Fusio\Impl\Table\Generated\LogRow $record): int
    {
        return $this->doCreate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\LogRow $record): int
    {
        return $this->doUpdate($record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function updateBy(\PSX\Sql\Condition $condition, \Fusio\Impl\Table\Generated\LogRow $record): int
    {
        return $this->doUpdateBy($condition, $record->toRecord());
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\LogRow $record): int
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
    protected function newRecord(array $row): \Fusio\Impl\Table\Generated\LogRow
    {
        return \Fusio\Impl\Table\Generated\LogRow::from($row);
    }
}