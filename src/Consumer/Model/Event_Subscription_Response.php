<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Event_Subscription_Response implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var int|null
     */
    protected $code;
    /**
     * @var string|null
     */
    protected $attempts;
    /**
     * @var string|null
     */
    protected $executeDate;
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
     * @param int|null $code
     */
    public function setCode(?int $code) : void
    {
        $this->code = $code;
    }
    /**
     * @return int|null
     */
    public function getCode() : ?int
    {
        return $this->code;
    }
    /**
     * @param string|null $attempts
     */
    public function setAttempts(?string $attempts) : void
    {
        $this->attempts = $attempts;
    }
    /**
     * @return string|null
     */
    public function getAttempts() : ?string
    {
        return $this->attempts;
    }
    /**
     * @param string|null $executeDate
     */
    public function setExecuteDate(?string $executeDate) : void
    {
        $this->executeDate = $executeDate;
    }
    /**
     * @return string|null
     */
    public function getExecuteDate() : ?string
    {
        return $this->executeDate;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('status' => $this->status, 'code' => $this->code, 'attempts' => $this->attempts, 'executeDate' => $this->executeDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
