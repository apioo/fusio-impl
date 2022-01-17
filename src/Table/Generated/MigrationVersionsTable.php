<?php

namespace Fusio\Impl\Table\Generated;

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
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\MigrationVersionsRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(string $version) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('version', $version);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\MigrationVersionsRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByVersion(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('version', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByVersion(string $value) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('version', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\MigrationVersionsRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByExecutedAt(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('executed_at', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByExecutedAt(\DateTime $value) : ?\Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('executed_at', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\MigrationVersionsRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\MigrationVersionsRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\MigrationVersionsRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\MigrationVersionsRow
    {
        return new \Fusio\Impl\Table\Generated\MigrationVersionsRow($row);
    }
}