<?php

namespace Fusio\Impl\Table\Generated;

class ProviderRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setType(?string $type) : void
    {
        $this->setProperty('type', $type);
    }
    public function getType() : ?string
    {
        return $this->getProperty('type');
    }
    public function setClass(?string $class) : void
    {
        $this->setProperty('class', $class);
    }
    public function getClass() : ?string
    {
        return $this->getProperty('class');
    }
}