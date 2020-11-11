<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Plan_Invoice implements \JsonSerializable
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
     * @var float|null
     */
    protected $amount;
    /**
     * @var int|null
     */
    protected $points;
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
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'amount' => $this->amount, 'points' => $this->points, 'payDate' => $this->payDate, 'insertDate' => $this->insertDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
