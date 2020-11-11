<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Audit_Collection_Query extends \Fusio\Impl\Model\Collection_Query implements \JsonSerializable
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
     * @var string|null
     */
    protected $event;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var string|null
     */
    protected $message;
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
     * @param string|null $event
     */
    public function setEvent(?string $event) : void
    {
        $this->event = $event;
    }
    /**
     * @return string|null
     */
    public function getEvent() : ?string
    {
        return $this->event;
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
     * @param string|null $message
     */
    public function setMessage(?string $message) : void
    {
        $this->message = $message;
    }
    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->message;
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
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('from' => $this->from, 'to' => $this->to, 'appId' => $this->appId, 'userId' => $this->userId, 'event' => $this->event, 'ip' => $this->ip, 'message' => $this->message, 'search' => $this->search), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
