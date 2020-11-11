<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


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
     * @var \DateTime|null
     */
    protected $updateDate;
    /**
     * @var \DateTime|null
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
     * @param \DateTime|null $updateDate
     */
    public function setUpdateDate(?\DateTime $updateDate) : void
    {
        $this->updateDate = $updateDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getUpdateDate() : ?\DateTime
    {
        return $this->updateDate;
    }
    /**
     * @param \DateTime|null $insertDate
     */
    public function setInsertDate(?\DateTime $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getInsertDate() : ?\DateTime
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
