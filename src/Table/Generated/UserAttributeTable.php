<?php

namespace Fusio\Impl\Table\Generated;

class UserAttributeTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_user_attribute';
    public const COLUMN_ID = 'id';
    public const COLUMN_USER_ID = 'user_id';
    public const COLUMN_NAME = 'name';
    public const COLUMN_VALUE = 'value';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_USER_ID => 0x20000a, self::COLUMN_NAME => 0xa000ff, self::COLUMN_VALUE => 0xa000ff);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUserId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUserId(int $value) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('user_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByName(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByName(string $value) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('name', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\UserAttributeRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByValue(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('value', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByValue(string $value) : ?\Fusio\Impl\Table\Generated\UserAttributeRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('value', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\UserAttributeRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\UserAttributeRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\UserAttributeRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\UserAttributeRow
    {
        return new \Fusio\Impl\Table\Generated\UserAttributeRow($row);
    }
}