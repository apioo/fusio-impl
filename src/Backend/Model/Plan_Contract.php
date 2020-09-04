<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Plan_Contract implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var User|null
     */
    protected $user;
    /**
     * @var Plan|null
     */
    protected $plan;
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
     * @var int|null
     */
    protected $period;
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
        return (object) array_filter(array('id' => $this->id, 'user' => $this->user, 'plan' => $this->plan, 'status' => $this->status, 'amount' => $this->amount, 'points' => $this->points, 'period' => $this->period, 'insertDate' => $this->insertDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
