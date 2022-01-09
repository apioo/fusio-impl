<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\AuditRow>
 */
class AuditTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_audit';
    public const COLUMN_ID = 'id';
    public const COLUMN_APP_ID = 'app_id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_REF_ID = 'ref_id';
    public const COLUMN_EVENT = 'event';
    public const COLUMN_IP = 'ip';
    public const COLUMN_MESSAGE = 'message';
    public const COLUMN_CONTENT = 'content';
    public const COLUMN_DATE = 'date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_APP_ID => 0x200000, self::COLUMN_USER_ID => 0x200000, self::COLUMN_REF_ID => 0x40200000, self::COLUMN_EVENT => 0xa000ff, self::COLUMN_IP => 0xa00028, self::COLUMN_MESSAGE => 0xa000ff, self::COLUMN_CONTENT => 0x40b00000, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByAppId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByRefId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('ref_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRefId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('ref_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByEvent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('event', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByEvent(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('event', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByIp(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByIp(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByMessage(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMessage(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByContent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('content', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByContent(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('content', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\AuditRow[]
     */
    public function findByDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\AuditRow';
    }
}