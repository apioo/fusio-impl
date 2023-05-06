<?php

namespace Fusio\Impl\Table\Generated;

class RoutesRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $status = null;
    private ?int $priority = null;
    private ?string $methods = null;
    private ?string $path = null;
    private ?string $controller = null;
    private ?string $metadata = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setCategoryId(int $categoryId) : void
    {
        $this->categoryId = $categoryId;
    }
    public function getCategoryId() : int
    {
        return $this->categoryId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "category_id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setPriority(?int $priority) : void
    {
        $this->priority = $priority;
    }
    public function getPriority() : ?int
    {
        return $this->priority;
    }
    public function setMethods(string $methods) : void
    {
        $this->methods = $methods;
    }
    public function getMethods() : string
    {
        return $this->methods ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "methods" was provided');
    }
    public function setPath(string $path) : void
    {
        $this->path = $path;
    }
    public function getPath() : string
    {
        return $this->path ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "path" was provided');
    }
    public function setController(string $controller) : void
    {
        $this->controller = $controller;
    }
    public function getController() : string
    {
        return $this->controller ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "controller" was provided');
    }
    public function setMetadata(?string $metadata) : void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata() : ?string
    {
        return $this->metadata;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('category_id', $this->categoryId);
        $record->put('status', $this->status);
        $record->put('priority', $this->priority);
        $record->put('methods', $this->methods);
        $record->put('path', $this->path);
        $record->put('controller', $this->controller);
        $record->put('metadata', $this->metadata);
        return $record;
    }
    public function jsonSerialize() : object
    {
        return (object) $this->toRecord()->getAll();
    }
    public static function from(array|\ArrayAccess $data) : self
    {
        $row = new self();
        $row->id = isset($data['id']) && is_int($data['id']) ? $data['id'] : null;
        $row->categoryId = isset($data['category_id']) && is_int($data['category_id']) ? $data['category_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->priority = isset($data['priority']) && is_int($data['priority']) ? $data['priority'] : null;
        $row->methods = isset($data['methods']) && is_string($data['methods']) ? $data['methods'] : null;
        $row->path = isset($data['path']) && is_string($data['path']) ? $data['path'] : null;
        $row->controller = isset($data['controller']) && is_string($data['controller']) ? $data['controller'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}