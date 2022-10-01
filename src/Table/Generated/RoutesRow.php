<?php

namespace Fusio\Impl\Table\Generated;

class RoutesRow extends \PSX\Record\Record
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
    public function setPriority(?int $priority) : void
    {
        $this->setProperty('priority', $priority);
    }
    public function getPriority() : ?int
    {
        return $this->getProperty('priority');
    }
    public function setMethods(?string $methods) : void
    {
        $this->setProperty('methods', $methods);
    }
    public function getMethods() : ?string
    {
        return $this->getProperty('methods');
    }
    public function setPath(?string $path) : void
    {
        $this->setProperty('path', $path);
    }
    public function getPath() : ?string
    {
        return $this->getProperty('path');
    }
    public function setController(?string $controller) : void
    {
        $this->setProperty('controller', $controller);
    }
    public function getController() : ?string
    {
        return $this->getProperty('controller');
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