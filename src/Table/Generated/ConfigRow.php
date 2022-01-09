<?php

namespace Fusio\Impl\Table\Generated;

class ConfigRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setType(?int $type) : void
    {
        $this->setProperty('type', $type);
    }
    public function getType() : ?int
    {
        return $this->getProperty('type');
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
    public function setValue(?string $value) : void
    {
        $this->setProperty('value', $value);
    }
    public function getValue() : ?string
    {
        return $this->getProperty('value');
    }
}