<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Log_Collection_Query extends \Fusio\Impl\Model\Collection_Query implements \JsonSerializable
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
    protected $routeId;
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
    protected $ip;
    /**
     * @var string|null
     */
    protected $userAgent;
    /**
     * @var string|null
     */
    protected $method;
    /**
     * @var string|null
     */
    protected $path;
    /**
     * @var string|null
     */
    protected $header;
    /**
     * @var string|null
     */
    protected $body;
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
     * @param int|null $routeId
     */
    public function setRouteId(?int $routeId) : void
    {
        $this->routeId = $routeId;
    }
    /**
     * @return int|null
     */
    public function getRouteId() : ?int
    {
        return $this->routeId;
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
     * @param string|null $userAgent
     */
    public function setUserAgent(?string $userAgent) : void
    {
        $this->userAgent = $userAgent;
    }
    /**
     * @return string|null
     */
    public function getUserAgent() : ?string
    {
        return $this->userAgent;
    }
    /**
     * @param string|null $method
     */
    public function setMethod(?string $method) : void
    {
        $this->method = $method;
    }
    /**
     * @return string|null
     */
    public function getMethod() : ?string
    {
        return $this->method;
    }
    /**
     * @param string|null $path
     */
    public function setPath(?string $path) : void
    {
        $this->path = $path;
    }
    /**
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
    }
    /**
     * @param string|null $header
     */
    public function setHeader(?string $header) : void
    {
        $this->header = $header;
    }
    /**
     * @return string|null
     */
    public function getHeader() : ?string
    {
        return $this->header;
    }
    /**
     * @param string|null $body
     */
    public function setBody(?string $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return string|null
     */
    public function getBody() : ?string
    {
        return $this->body;
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
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('from' => $this->from, 'to' => $this->to, 'routeId' => $this->routeId, 'appId' => $this->appId, 'userId' => $this->userId, 'ip' => $this->ip, 'userAgent' => $this->userAgent, 'method' => $this->method, 'path' => $this->path, 'header' => $this->header, 'body' => $this->body, 'search' => $this->search), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
