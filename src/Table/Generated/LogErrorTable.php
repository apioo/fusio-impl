<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\LogErrorRow>
 */
class LogErrorTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_log_error';
    public const COLUMN_ID = 'id';
    public const COLUMN_LOG_ID = 'log_id';
    public const COLUMN_MESSAGE = 'message';
    public const COLUMN_TRACE = 'trace';
    public const COLUMN_FILE = 'file';
    public const COLUMN_LINE = 'line';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_LOG_ID => 0x200000, self::COLUMN_MESSAGE => 0xa001f4, self::COLUMN_TRACE => 0xb00000, self::COLUMN_FILE => 0xa000ff, self::COLUMN_LINE => 0x200000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findByLogId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('log_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByLogId(int $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('log_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findByMessage(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMessage(string $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('message', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findByTrace(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('trace', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByTrace(string $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('trace', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findByFile(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('file', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByFile(string $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('file', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\LogErrorRow[]
     */
    public function findByLine(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('line', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByLine(int $value) : ?\Fusio\Impl\Table\Generated\LogErrorRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('line', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\LogErrorRow';
    }
}