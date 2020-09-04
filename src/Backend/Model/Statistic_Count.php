<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Statistic_Count implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $count;
    /**
     * @var \DateTime|null
     */
    protected $from;
    /**
     * @var \DateTime|null
     */
    protected $to;
    /**
     * @param int|null $count
     */
    public function setCount(?int $count) : void
    {
        $this->count = $count;
    }
    /**
     * @return int|null
     */
    public function getCount() : ?int
    {
        return $this->count;
    }
    /**
     * @param \DateTime|null $from
     */
    public function setFrom(?\DateTime $from) : void
    {
        $this->from = $from;
    }
    /**
     * @return \DateTime|null
     */
    public function getFrom() : ?\DateTime
    {
        return $this->from;
    }
    /**
     * @param \DateTime|null $to
     */
    public function setTo(?\DateTime $to) : void
    {
        $this->to = $to;
    }
    /**
     * @return \DateTime|null
     */
    public function getTo() : ?\DateTime
    {
        return $this->to;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('count' => $this->count, 'from' => $this->from, 'to' => $this->to), static function ($value) : bool {
            return $value !== null;
        });
    }
}
