<?php

namespace Fusio\Impl\Table\Generated;

class PlanRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
    }
    public function setDescription(?string $description) : void
    {
        $this->setProperty('description', $description);
    }
    public function getDescription() : ?string
    {
        return $this->getProperty('description');
    }
    public function setPrice(?float $price) : void
    {
        $this->setProperty('price', $price);
    }
    public function getPrice() : ?float
    {
        return $this->getProperty('price');
    }
    public function setPoints(?int $points) : void
    {
        $this->setProperty('points', $points);
    }
    public function getPoints() : ?int
    {
        return $this->getProperty('points');
    }
    public function setPeriodType(?int $periodType) : void
    {
        $this->setProperty('period_type', $periodType);
    }
    public function getPeriodType() : ?int
    {
        return $this->getProperty('period_type');
    }
    public function setPeriodCount(?int $periodCount) : void
    {
        $this->setProperty('period_count', $periodCount);
    }
    public function getPeriodCount() : ?int
    {
        return $this->getProperty('period_count');
    }
    public function setExternalId(?string $externalId) : void
    {
        $this->setProperty('external_id', $externalId);
    }
    public function getExternalId() : ?string
    {
        return $this->getProperty('external_id');
    }
}