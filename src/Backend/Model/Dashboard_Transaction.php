<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Dashboard_Transaction implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $status;
    /**
     * @var string|null
     */
    protected $provider;
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
    protected $date;
    /**
     * @param string|null $status
     */
    public function setStatus(?string $status) : void
    {
        $this->status = $status;
    }
    /**
     * @return string|null
     */
    public function getStatus() : ?string
    {
        return $this->status;
    }
    /**
     * @param string|null $provider
     */
    public function setProvider(?string $provider) : void
    {
        $this->provider = $provider;
    }
    /**
     * @return string|null
     */
    public function getProvider() : ?string
    {
        return $this->provider;
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
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date) : void
    {
        $this->date = $date;
    }
    /**
     * @return \DateTime|null
     */
    public function getDate() : ?\DateTime
    {
        return $this->date;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('status' => $this->status, 'provider' => $this->provider, 'transactionId' => $this->transactionId, 'amount' => $this->amount, 'date' => $this->date), static function ($value) : bool {
            return $value !== null;
        });
    }
}
