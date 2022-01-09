<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\MigrationVersionsRow>
 */
class MigrationVersionsTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_migration_versions';
    public const COLUMN_VERSION = 'version';
    public const COLUMN_EXECUTED_AT = 'executed_at';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_VERSION => 0x10a0000e, self::COLUMN_EXECUTED_AT => 0xa00000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\MigrationVersionsRow[]
     */
    public function findByVersion(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('version', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByVersion(string $value) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('version', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\MigrationVersionsRow[]
     */
    public function findByExecutedAt(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('executed_at', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByExecutedAt(\DateTime $value) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('executed_at', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\MigrationVersionsRow';
    }
}