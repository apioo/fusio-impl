<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"contractId", "startDate"})
 */
class Plan_Invoice_Create implements \JsonSerializable
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
     * @var \DateTime|null
     */
    protected $startDate;
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
     * @param \DateTime|null $startDate
     */
    public function setStartDate(?\DateTime $startDate) : void
    {
        $this->startDate = $startDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getStartDate() : ?\DateTime
    {
        return $this->startDate;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'contractId' => $this->contractId, 'startDate' => $this->startDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
