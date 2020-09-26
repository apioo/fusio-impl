<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Transaction_Prepare_Response implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $approvalUrl;
    /**
     * @param int|null $approvalUrl
     */
    public function setApprovalUrl(?int $approvalUrl) : void
    {
        $this->approvalUrl = $approvalUrl;
    }
    /**
     * @return int|null
     */
    public function getApprovalUrl() : ?int
    {
        return $this->approvalUrl;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('approvalUrl' => $this->approvalUrl), static function ($value) : bool {
            return $value !== null;
        });
    }
}
