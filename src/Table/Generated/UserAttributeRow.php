<?php

namespace Fusio\Impl\Table\Generated;

class UserAttributeRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setUserId(?int $userId) : void
    {
        $this->setProperty('user_id', $userId);
    }
    public function getUserId() : ?int
    {
        return $this->getProperty('user_id');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
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