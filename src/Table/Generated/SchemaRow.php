<?php

namespace Fusio\Impl\Table\Generated;

class SchemaRow extends \PSX\Record\Record
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
    public function setSource(?string $source) : void
    {
        $this->setProperty('source', $source);
    }
    public function getSource() : ?string
    {
        return $this->getProperty('source');
    }
    public function setForm(?string $form) : void
    {
        $this->setProperty('form', $form);
    }
    public function getForm() : ?string
    {
        return $this->getProperty('form');
    }
    public function setMetadata(?string $metadata) : void
    {
        $this->setProperty('metadata', $metadata);
    }
    public function getMetadata() : ?string
    {
        return $this->getProperty('metadata');
    }
}