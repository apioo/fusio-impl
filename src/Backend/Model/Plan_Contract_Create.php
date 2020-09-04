<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"userId", "planId"})
 */
class Plan_Contract_Create implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $userId;
    /**
     * @var int|null
     */
    protected $planId;
    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId) : void
    {
        $this->userId = $userId;
    }
    /**
     * @return int|null
     */
    public function getUserId() : ?int
    {
        return $this->userId;
    }
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
        return (object) array_filter(array('userId' => $this->userId, 'planId' => $this->planId), static function ($value) : bool {
            return $value !== null;
        });
    }
}
