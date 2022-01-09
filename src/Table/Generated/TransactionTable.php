<?php

namespace Fusio\Impl\Table\Generated;

/**
 * @extends \PSX\Sql\TableAbstract<\Fusio\Impl\Table\Generated\TransactionRow>
 */
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
        return array(self::COLUMN_ID => 0x30200000, self::COLUMN_INVOICE_ID => 0x200000, self::COLUMN_STATUS => 0x200000, self::COLUMN_PROVIDER => 0xa000ff, self::COLUMN_TRANSACTION_ID => 0xa000ff, self::COLUMN_REMOTE_ID => 0x40a000ff, self::COLUMN_AMOUNT => 0x500000, self::COLUMN_RETURN_URL => 0xa000ff, self::COLUMN_UPDATE_DATE => 0x40800000, self::COLUMN_INSERT_DATE => 0x800000);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findById(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneById(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByInvoiceId(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('invoice_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByInvoiceId(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('invoice_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByStatus(int $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByStatus(int $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('status', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByProvider(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('provider', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByProvider(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('provider', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByTransactionId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('transaction_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByTransactionId(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('transaction_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByRemoteId(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('remote_id', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByRemoteId(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('remote_id', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByAmount(float $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByAmount(float $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('amount', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByReturnUrl(string $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('return_url', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByReturnUrl(string $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->like('return_url', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByUpdateDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('update_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByUpdateDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('update_date', $value);
        return $this->findOneBy($condition, null);
    }
    /**
     * @return \Fusio\Impl\Table\Generated\TransactionRow[]
     */
    public function findByInsertDate(\DateTime $value, ?int $startIndex = null, ?int $count = null, ?string $sortBy = null, ?int $sortOrder = null) : iterable
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findBy($condition, null, $startIndex, $count, $sortBy, $sortOrder);
    }
    public function findOneByInsertDate(\DateTime $value) : ?\Fusio\Impl\Table\Generated\TransactionRow
    {
        $condition = new \PSX\Sql\Condition();
        $condition->equals('insert_date', $value);
        return $this->findOneBy($condition, null);
    }
    protected function getRecordClass() : string
    {
        return '\\Fusio\\Impl\\Table\\Generated\\TransactionRow';
    }
}