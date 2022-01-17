<?php

namespace Fusio\Impl\Table\Generated;

class TransactionTable extends \PSX\Sql\TableAbstract
{
    public const NAME = 'fusio_transaction';
    public const COLUMN_ID = 'id';
    public const COLUMN_INVOICE_ID = 'invoice_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_PROVIDER = 'provider';
    public const COLUMN_TRANSACTION_ID = 'transaction_id';
    public const COLUMN_REMOTE_ID = 'remote_id';
    public const COLUMN_AMOUNT = 'amount';
    public const COLUMN_RETURN_URL = 'return_url';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_INSERT_DATE = 'insert_date';
    public function getName() : string
    {
        return self::NAME;
    }
    public function getColumns() : array
    {
        return array(self::COLUMN_ID => 0x3020000a, self::COLUMN_INVOICE_ID => 0x20000a, self::COLUMN_STATUS => 0x20000a, self::COLUMN_PROVIDER => 0xa000ff, self::COLUMN_TRANSACTION_ID => 0xa000ff, self::COLUMN_REMOTE_ID => 0x40a000ff, self::COLUMN_AMOUNT => 0x500000, self::COLUMN_RETURN_URL => 0xa000ff, self::COLUMN_UPDATE_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findAll(?\PSX\Sql\Condition $condition = null, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindAll($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findBy(\PSX\Sql\Condition $condition, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null, ?\PSX\Sql\Fields $fields = null) : iterable
    {
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneBy(\PSX\Sql\Condition $condition, ?\PSX\Sql\Fields $fields = null) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        return $this->doFindOneBy($condition, $fields);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function find(int $id) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $id);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
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
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInvoiceId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('invoice_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInvoiceId(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('invoice_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByProvider(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('provider', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByProvider(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('provider', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByTransactionId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('transaction_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByTransactionId(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('transaction_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByRemoteId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('remote_id', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByRemoteId(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('remote_id', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByAmount(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByAmount(float $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByReturnUrl(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('return_url', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByReturnUrl(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('return_url', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByUpdateDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('update_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByUpdateDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('update_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findByInsertDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->doFindBy($condition, $startIndex, $count, $sortBy, $sortOrder);
    }
    /**
     * @throws \PSX\Sql\Exception\QueryException
     */
    public function findOneByInsertDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->doFindOneBy($condition);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function create(\Fusio\Impl\Table\Generated\TransactionRow $record) : int
    {
        return $this->doCreate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function update(\Fusio\Impl\Table\Generated\TransactionRow $record) : int
    {
        return $this->doUpdate($record);
    }
    /**
     * @throws \PSX\Sql\Exception\ManipulationException
     */
    public function delete(\Fusio\Impl\Table\Generated\TransactionRow $record) : int
    {
        return $this->doDelete($record);
    }
    protected function newRecord(array $row) : \Fusio\Impl\Table\Generated\TransactionRow
    {
        return new \Fusio\Impl\Table\Generated\TransactionRow($row);
    }
}