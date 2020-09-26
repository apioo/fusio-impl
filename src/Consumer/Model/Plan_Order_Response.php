<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Plan_Order_Response implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $contractId;
    /**
     * @var int|null
     */
    protected $invoiceId;
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
     * @param int|null $invoiceId
     */
    public function setInvoiceId(?int $invoiceId) : void
    {
        $this->invoiceId = $invoiceId;
    }
    /**
     * @return int|null
     */
    public function getInvoiceId() : ?int
    {
        return $this->invoiceId;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('contractId' => $this->contractId, 'invoiceId' => $this->invoiceId), static function ($value) : bool {
            return $value !== null;
        });
    }
}
