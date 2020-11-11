<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Plan_Contract implements \JsonSerializable
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
     * @var Plan|null
     */
    protected $plan;
    /**
     * @var float|null
     */
    protected $amount;
    /**
     * @var int|null
     */
    protected $points;
    /**
     * @var int|null
     */
    protected $period;
    /**
     * @var array<Plan_Invoice>|null
     */
    protected $invoices;
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
     * @param Plan|null $plan
     */
    public function setPlan(?Plan $plan) : void
    {
        $this->plan = $plan;
    }
    /**
     * @return Plan|null
     */
    public function getPlan() : ?Plan
    {
        return $this->plan;
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
     * @param int|null $period
     */
    public function setPeriod(?int $period) : void
    {
        $this->period = $period;
    }
    /**
     * @return int|null
     */
    public function getPeriod() : ?int
    {
        return $this->period;
    }
    /**
     * @param array<Plan_Invoice>|null $invoices
     */
    public function setInvoices(?array $invoices) : void
    {
        $this->invoices = $invoices;
    }
    /**
     * @return array<Plan_Invoice>|null
     */
    public function getInvoices() : ?array
    {
        return $this->invoices;
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
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'plan' => $this->plan, 'amount' => $this->amount, 'points' => $this->points, 'period' => $this->period, 'invoices' => $this->invoices, 'insertDate' => $this->insertDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
