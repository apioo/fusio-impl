<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Plan_Invoice implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $contractId;
    /**
     * @var User|null
     */
    protected $user;
    /**
     * @var int|null
     */
    protected $transactionId;
    /**
     * @var int|null
     */
    protected $prevId;
    /**
     * @var string|null
     */
    protected $displayId;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var float|null
     */
    protected $amount;
    /**
     * @var int|null
     */
    protected $points;
    /**
     * @var \PSX\DateTime\Date|null
     */
    protected $fromDate;
    /**
     * @var \PSX\DateTime\Date|null
     */
    protected $toDate;
    /**
     * @var \DateTime|null
     */
    protected $payDate;
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
     * @param int|null $contractId
     */
    public function setContractId(?int $contractId) : void
    {
        $this->contractId = $contractId;
    }
    /**
     * @return int|null
     */
    public function getContractId() : ?int
    {
        return $this->contractId;
    }
    /**
     * @param User|null $user
     */
    public function setUser(?User $user) : void
    {
        $this->user = $user;
    }
    /**
     * @return User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }
    /**
     * @param int|null $transactionId
     */
    public function setTransactionId(?int $transactionId) : void
    {
        $this->transactionId = $transactionId;
    }
    /**
     * @return int|null
     */
    public function getTransactionId() : ?int
    {
        return $this->transactionId;
    }
    /**
     * @param int|null $prevId
     */
    public function setPrevId(?int $prevId) : void
    {
        $this->prevId = $prevId;
    }
    /**
     * @return int|null
     */
    public function getPrevId() : ?int
    {
        return $this->prevId;
    }
    /**
     * @param string|null $displayId
     */
    public function setDisplayId(?string $displayId) : void
    {
        $this->displayId = $displayId;
    }
    /**
     * @return string|null
     */
    public function getDisplayId() : ?string
    {
        return $this->displayId;
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
     * @param int|null $points
     */
    public function setPoints(?int $points) : void
    {
        $this->points = $points;
    }
    /**
     * @return int|null
     */
    public function getPoints() : ?int
    {
        return $this->points;
    }
    /**
     * @param \PSX\DateTime\Date|null $fromDate
     */
    public function setFromDate(?\PSX\DateTime\Date $fromDate) : void
    {
        $this->fromDate = $fromDate;
    }
    /**
     * @return \PSX\DateTime\Date|null
     */
    public function getFromDate() : ?\PSX\DateTime\Date
    {
        return $this->fromDate;
    }
    /**
     * @param \PSX\DateTime\Date|null $toDate
     */
    public function setToDate(?\PSX\DateTime\Date $toDate) : void
    {
        $this->toDate = $toDate;
    }
    /**
     * @return \PSX\DateTime\Date|null
     */
    public function getToDate() : ?\PSX\DateTime\Date
    {
        return $this->toDate;
    }
    /**
     * @param \DateTime|null $payDate
     */
    public function setPayDate(?\DateTime $payDate) : void
    {
        $this->payDate = $payDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getPayDate() : ?\DateTime
    {
        return $this->payDate;
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
        return (object) array_filter(array('id' => $this->id, 'contractId' => $this->contractId, 'user' => $this->user, 'transactionId' => $this->transactionId, 'prevId' => $this->prevId, 'displayId' => $this->displayId, 'status' => $this->status, 'amount' => $this->amount, 'points' => $this->points, 'fromDate' => $this->fromDate, 'toDate' => $this->toDate, 'payDate' => $this->payDate, 'insertDate' => $this->insertDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
