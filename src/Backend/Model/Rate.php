<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Rate implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     * @Minimum(0)
     */
    protected $priority;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var int|null
     * @Minimum(0)
     */
    protected $rateLimit;
    /**
     * @var \DateInterval|null
     */
    protected $timespan;
    /**
     * @var array<Rate_Allocation>|null
     */
    protected $allocations;
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
     * @param int|null $priority
     */
    public function setPriority(?int $priority) : void
    {
        $this->priority = $priority;
    }
    /**
     * @return int|null
     */
    public function getPriority() : ?int
    {
        return $this->priority;
    }
    /**
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param int|null $rateLimit
     */
    public function setRateLimit(?int $rateLimit) : void
    {
        $this->rateLimit = $rateLimit;
    }
    /**
     * @return int|null
     */
    public function getRateLimit() : ?int
    {
        return $this->rateLimit;
    }
    /**
     * @param \DateInterval|null $timespan
     */
    public function setTimespan(?\DateInterval $timespan) : void
    {
        $this->timespan = $timespan;
    }
    /**
     * @return \DateInterval|null
     */
    public function getTimespan() : ?\DateInterval
    {
        return $this->timespan;
    }
    /**
     * @param array<Rate_Allocation>|null $allocations
     */
    public function setAllocations(?array $allocations) : void
    {
        $this->allocations = $allocations;
    }
    /**
     * @return array<Rate_Allocation>|null
     */
    public function getAllocations() : ?array
    {
        return $this->allocations;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'priority' => $this->priority, 'name' => $this->name, 'rateLimit' => $this->rateLimit, 'timespan' => $this->timespan, 'allocations' => $this->allocations), static function ($value) : bool {
            return $value !== null;
        });
    }
}
