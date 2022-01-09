<?php

namespace Fusio\Impl\Table\Generated;

class ActionRow extends \PSX\Record\Record
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
    public function setClass(?string $class) : void
    {
        $this->setProperty('class', $class);
    }
    public function getClass() : ?string
    {
        return $this->getProperty('class');
    }
    public function setAsync(?bool $async) : void
    {
        $this->setProperty('async', $async);
    }
    public function getAsync() : ?bool
    {
        return $this->getProperty('async');
    }
    public function setEngine(?string $engine) : void
    {
        $this->setProperty('engine', $engine);
    }
    public function getEngine() : ?string
    {
        return $this->getProperty('engine');
    }
    public function setConfig(?string $config) : void
    {
        $this->setProperty('config', $config);
    }
    public function getConfig() : ?string
    {
        return $this->getProperty('config');
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