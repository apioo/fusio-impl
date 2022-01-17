<?php

namespace Fusio\Impl\Table\Generated;

class LogRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setCategoryId(?int $categoryId) : void
    {
        $this->setProperty('category_id', $categoryId);
    }
    public function getCategoryId() : ?int
    {
        return $this->getProperty('category_id');
    }
    public function setRouteId(?int $routeId) : void
    {
        $this->setProperty('route_id', $routeId);
    }
    public function getRouteId() : ?int
    {
        return $this->getProperty('route_id');
    }
    public function setAppId(?int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : ?int
    {
        return $this->getProperty('app_id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setIp(?string $ip) : void
    {
        $this->setProperty('ip', $ip);
    }
    public function getIp() : ?string
    {
        return $this->getProperty('ip');
    }
    public function setUserAgent(?string $userAgent) : void
    {
        $this->setProperty('user_agent', $userAgent);
    }
    public function getUserAgent() : ?string
    {
        return $this->getProperty('user_agent');
    }
    public function setMethod(?string $method) : void
    {
        $this->setProperty('method', $method);
    }
    public function getMethod() : ?string
    {
        return $this->getProperty('method');
    }
    public function setPath(?string $path) : void
    {
        $this->setProperty('path', $path);
    }
    public function getPath() : ?string
    {
        return $this->getProperty('path');
    }
    public function setHeader(?string $header) : void
    {
        $this->setProperty('header', $header);
    }
    public function getHeader() : ?string
    {
        return $this->getProperty('header');
    }
    public function setBody(?string $body) : void
    {
        $this->setProperty('body', $body);
    }
    public function getBody() : ?string
    {
        return $this->getProperty('body');
    }
    public function setExecutionTime(?int $executionTime) : void
    {
        $this->setProperty('execution_time', $executionTime);
    }
    public function getExecutionTime() : ?int
    {
        return $this->getProperty('execution_time');
    }
    public function setDate(?\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : ?\DateTime
    {
        return $this->getProperty('date');
    }
}