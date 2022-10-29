<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\LogRow>
 */
class LogTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_log';
    public const COLUMN_ID = 'id';
    public const COLUMN_CATEGORY_ID = 'category_id';
    public const COLUMN_ROUTE_ID = 'route_id';
    public const COLUMN_APP_ID = 'app_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_IP = 'ip';
    public const COLUMN_USER_AGENT = 'user_agent';
    public const COLUMN_METHOD = 'method';
    public const COLUMN_PATH = 'path';
    public const COLUMN_HEADER = 'header';
    public const COLUMN_BODY = 'body';
    public const COLUMN_EXECUTION_TIME = 'execution_time';
    public const COLUMN_DATE = 'date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_CATEGORY_ID => 0x20000a, self::COLUMN_ROUTE_ID => 0x4020000a, self::COLUMN_APP_ID => 0x4020000a, self::COLUMN_USER_ID => 0x4020000a, self::COLUMN_IP => 0xa00028, self::COLUMN_USER_AGENT => 0xa000ff, self::COLUMN_METHOD => 0xa00010, self::COLUMN_PATH => 0xa003ff, self::COLUMN_HEADER => 0xb00000, self::COLUMN_BODY => 0x40b00000, self::COLUMN_EXECUTION_TIME => 0x4020000a, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCategoryId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('category_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCategoryId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('category_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByIp(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByIp(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserAgent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('user_agent', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserAgent(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('user_agent', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMethod(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMethod(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByPath(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('path', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByPath(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('path', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByHeader(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('header', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByHeader(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('header', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByBody(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('body', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByBody(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('body', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExecutionTime(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execution_time', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExecutionTime(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execution_time', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\LogRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\LogRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\LogRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\LogRow $record) : int
    {
        return $this->doDelete($record);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\LogRow
    {
        return new \Fusio\Impl\Table\Generated\LogRow($row);
    }
}