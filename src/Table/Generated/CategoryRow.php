<?php

namespace Fusio\Impl\Table\Generated;

class CategoryRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setStatus(int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : int
    {
        return $this->getProperty('status');
    }
    public function setName(string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : string
    {
        return $this->getProperty('name');
    }
}