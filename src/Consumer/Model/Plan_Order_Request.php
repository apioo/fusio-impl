<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"planId"})
 */
class Plan_Order_Request implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $planId;
    /**
     * @param int|null $planId
     */
    public function setPlanId(?int $planId) : void
    {
        $this->planId = $planId;
    }
    /**
     * @return int|null
     */
    public function getPlanId() : ?int
    {
        return $this->planId;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('planId' => $this->planId), static function ($value) : bool {
            return $value !== null;
        });
    }
}
