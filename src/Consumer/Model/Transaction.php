<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Transaction implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     */
    protected $transactionId;
    /**
     * @var float|null
     */
    protected $amount;
    /**
     * @var int|null
     */
    protected $updateDate;
    /**
     * @var int|null
     */
    protected $insertDate;
    /**
     * @param int|null $id
     */
    public function setId(?int $id) : void
    {
        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }
    /**
     * @param int|null $status
     */
    public function setStatus(?int $status) : void
    {
        $this->status = $status;
    }
    /**
     * @return int|null
     */
    public function getStatus() : ?int
    {
        return $this->status;
    }
    /**
     * @param string|null $transactionId
     */
    public function setTransactionId(?string $transactionId) : void
    {
        $this->transactionId = $transactionId;
    }
    /**
     * @return string|null
     */
    public function getTransactionId() : ?string
    {
        return $this->transactionId;
    }
    /**
     * @param float|null $amount
     */
    public function setAmount(?float $amount) : void
    {
        $this->amount = $amount;
    }
    /**
     * @return float|null
     */
    public function getAmount() : ?float
    {
        return $this->amount;
    }
    /**
     * @param int|null $updateDate
     */
    public function setUpdateDate(?int $updateDate) : void
    {
        $this->updateDate = $updateDate;
    }
    /**
     * @return int|null
     */
    public function getUpdateDate() : ?int
    {
        return $this->updateDate;
    }
    /**
     * @param int|null $insertDate
     */
    public function setInsertDate(?int $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    /**
     * @return int|null
     */
    public function getInsertDate() : ?int
    {
        return $this->insertDate;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'transactionId' => $this->transactionId, 'amount' => $this->amount, 'updateDate' => $this->updateDate, 'insertDate' => $this->insertDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
