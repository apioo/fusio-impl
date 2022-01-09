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
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_CATEGORY_ID => 0x200000, self::COLUMN_ROUTE_ID => 0x40200000, self::COLUMN_APP_ID => 0x40200000, self::COLUMN_USER_ID => 0x40200000, self::COLUMN_IP => 0xa00028, self::COLUMN_USER_AGENT => 0xa000ff, self::COLUMN_METHOD => 0xa00010, self::COLUMN_PATH => 0xa003ff, self::COLUMN_HEADER => 0xb00000, self::COLUMN_BODY => 0x40b00000, self::COLUMN_EXECUTION_TIME => 0x40200000, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByCategoryId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('category_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByCategoryId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('category_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByRouteId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRouteId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('route_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByIp(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByIp(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByUserAgent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('user_agent', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserAgent(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('user_agent', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByMethod(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMethod(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('method', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByPath(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('path', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByPath(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('path', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByHeader(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('header', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByHeader(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('header', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByBody(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('body', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByBody(string $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('body', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByExecutionTime(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execution_time', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByExecutionTime(int $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('execution_time', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogRow[]
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\LogRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\LogRow';
    }
}