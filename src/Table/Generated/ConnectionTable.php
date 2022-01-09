<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\ConnectionRow>
 */
class ConnectionTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_connection';
    public const COLUMN_ID = 'id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_NAME = 'name';
    public const COLUMN_CLASS = 'class';
    public const COLUMN_CONFIG = 'config';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_NAME => 0xa000ff, self::COLUMN_CLASS => 0xa000ff, self::COLUMN_CONFIG => 0x40b00000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ConnectionRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\ConnectionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ConnectionRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\ConnectionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ConnectionRow[]
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\ConnectionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ConnectionRow[]
     */
    public function findByClass(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('class', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByClass(string $value) : ?\Fusio\Impl\Table\Generated\ConnectionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('class', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\ConnectionRow[]
     */
    public function findByConfig(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('config', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByConfig(string $value) : ?\Fusio\Impl\Table\Generated\ConnectionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('config', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\ConnectionRow';
    }
}