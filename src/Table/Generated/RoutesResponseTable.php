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
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_METHOD_ID => 0x200000, self::COLUMN_CODE => 0x100000, self::COLUMN_RESPONSE => 0xa000ff);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesResponseRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesResponseRow[]
     */
    public function findByMethodId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('method_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByMethodId(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('method_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesResponseRow[]
     */
    public function findByCode(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByCode(int $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('code', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\RoutesResponseRow[]
     */
    public function findByResponse(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('response', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByResponse(string $value) : ?\Fusio\Impl\Table\Generated\RoutesResponseRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('response', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\RoutesResponseRow';
    }
}