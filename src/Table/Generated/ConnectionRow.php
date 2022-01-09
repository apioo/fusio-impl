<?php

namespace Fusio\Impl\Table\Generated;

class ConnectionRow extends \PSX\Record\Record
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
    public function setClass(?string $class) : void
    {
        $this->setProperty('class', $class);
    }
    public function getClass() : ?string
    {
        return $this->getProperty('class');
    }
    public function setConfig(?string $config) : void
    {
        $this->setProperty('config', $config);
    }
    public function getConfig() : ?string
    {
        return $this->getProperty('config');
    }
}