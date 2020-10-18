<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class App_Token_Collection_Query extends \Fusio\Impl\Model\Collection_Query implements \JsonSerializable
{
    /**
     * @var \DateTime|null
     */
    protected $from;
    /**
     * @var \DateTime|null
     */
    protected $to;
    /**
     * @var int|null
     */
    protected $appId;
    /**
     * @var int|null
     */
    protected $userId;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     */
    protected $scope;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var string|null
     */
    protected $search;
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
    /**
     * @param int|null $appId
     */
    public function setAppId(?int $appId) : void
    {
        $this->appId = $appId;
    }
    /**
     * @return int|null
     */
    public function getAppId() : ?int
    {
        return $this->appId;
    }
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
     * @param string|null $scope
     */
    public function setScope(?string $scope) : void
    {
        $this->scope = $scope;
    }
    /**
     * @return string|null
     */
    public function getScope() : ?string
    {
        return $this->scope;
    }
    /**
     * @param string|null $ip
     */
    public function setIp(?string $ip) : void
    {
        $this->ip = $ip;
    }
    /**
     * @return string|null
     */
    public function getIp() : ?string
    {
        return $this->ip;
    }
    /**
     * @param string|null $search
     */
    public function setSearch(?string $search) : void
    {
        $this->search = $search;
    }
    /**
     * @return string|null
     */
    public function getSearch() : ?string
    {
        return $this->search;
    }
    public function jsonSerialize()
    {
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('from' => $this->from, 'to' => $this->to, 'appId' => $this->appId, 'userId' => $this->userId, 'status' => $this->status, 'scope' => $this->scope, 'ip' => $this->ip, 'search' => $this->search), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
