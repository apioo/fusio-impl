<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\RoutesResponseRow>
 */
class RoutesResponseTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_routes_response';
    public const COLUMN_ID = 'id';
    public const COLUMN_METHOD_ID = 'method_id';
    public const COLUMN_CODE = 'code';
    public const COLUMN_RESPONSE = 'response';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_METHOD_ID => 0x20000a, self::COLUMN_CODE => 0x100000, self::COLUMN_RESPONSE => 0xa000ff);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : array
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByMethodId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('method_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByMethodId(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('method_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByCode(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByCode(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return array<\Fusio\Impl\Table\Generated\RoutesResponseRow>
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByResponse(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : array
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('response', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByResponse(string $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('response', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\RoutesResponseRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\RoutesResponseRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\RoutesResponseRow $record) : int
    {
        return $this->doDelete($record);
    }
    /**
     * @param array<string, mixed> $row
     */
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        return new \Fusio\Impl\Table\Generated\RoutesResponseRow($row);
    }
}