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
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_APP_ID => 0x20000a, self::COLUMN_USER_ID => 0x20000a, self::COLUMN_REF_ID => 0x4020000a, self::COLUMN_EVENT => 0xa000ff, self::COLUMN_IP => 0xa00028, self::COLUMN_MESSAGE => 0xa000ff, self::COLUMN_CONTENT => 0x40b00000, self::COLUMN_DATE => 0x800000);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
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
    public function findOneByAppId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('app_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
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
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRefId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('ref_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRefId(int $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('ref_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByEvent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('event', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByEvent(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('event', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
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
    public function findOneByIp(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('ip', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMessage(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMessage(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByContent(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('content', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByContent(string $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('content', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\AuditRow>
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
    public function findOneByDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\AuditRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\AuditRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\AuditRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\AuditRow $record) : int
    {
        return $this->doDelete($record);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\AuditRow
    {
        return new \Fusio\Impl\Table\Generated\AuditRow($row);
    }
}