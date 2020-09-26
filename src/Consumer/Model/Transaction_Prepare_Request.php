<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"invoiceId", "returnUrl"})
 */
class Transaction_Prepare_Request implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $invoiceId;
    /**
     * @var int|null
     */
    protected $returnUrl;
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
    /**
     * @param int|null $returnUrl
     */
    public function setReturnUrl(?int $returnUrl) : void
    {
        $this->returnUrl = $returnUrl;
    }
    /**
     * @return int|null
     */
    public function getReturnUrl() : ?int
    {
        return $this->returnUrl;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('invoiceId' => $this->invoiceId, 'returnUrl' => $this->returnUrl), static function ($value) : bool {
            return $value !== null;
        });
    }
}
