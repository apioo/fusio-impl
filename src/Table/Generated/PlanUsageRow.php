<?php

namespace Fusio\Impl\Table\Generated;

class PlanUsageRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setRouteId(int $routeId) : void
    {
        $this->setProperty('route_id', $routeId);
    }
    public function getRouteId() : int
    {
        return $this->getProperty('route_id');
    }
    public function setUserId(int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : int
    {
        return $this->getProperty('user_id');
    }
    public function setAppId(int $appId) : void
    {
        $this->setProperty('app_id', $appId);
    }
    public function getAppId() : int
    {
        return $this->getProperty('app_id');
    }
    public function setPoints(int $points) : void
    {
        $this->setProperty('points', $points);
    }
    public function getPoints() : int
    {
        return $this->getProperty('points');
    }
    public function setInsertDate(\DateTime $insertDate) : void
    {
        $this->setProperty('insert_date', $insertDate);
    }
    public function getInsertDate() : \DateTime
    {
        return $this->getProperty('insert_date');
    }
}