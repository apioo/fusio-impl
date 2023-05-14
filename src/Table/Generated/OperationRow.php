<?php

namespace Fusio\Impl\Table\Generated;

class OperationRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $status = null;
    private ?int $active = null;
    private ?int $public = null;
    private ?int $stability = null;
    private ?string $description = null;
    private ?string $httpMethod = null;
    private ?string $httpPath = null;
    private ?string $name = null;
    private ?string $parameters = null;
    private ?string $incoming = null;
    private ?string $outgoing = null;
    private ?string $throws = null;
    private ?string $action = null;
    private ?int $costs = null;
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
    public function setActive(int $active) : void
    {
        $this->active = $active;
    }
    public function getActive() : int
    {
        return $this->active ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "active" was provided');
    }
    public function setPublic(int $public) : void
    {
        $this->public = $public;
    }
    public function getPublic() : int
    {
        return $this->public ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "public" was provided');
    }
    public function setStability(int $stability) : void
    {
        $this->stability = $stability;
    }
    public function getStability() : int
    {
        return $this->stability ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "stability" was provided');
    }
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    public function getDescription() : ?string
    {
        return $this->description;
    }
    public function setHttpMethod(string $httpMethod) : void
    {
        $this->httpMethod = $httpMethod;
    }
    public function getHttpMethod() : string
    {
        return $this->httpMethod ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "http_method" was provided');
    }
    public function setHttpPath(string $httpPath) : void
    {
        $this->httpPath = $httpPath;
    }
    public function getHttpPath() : string
    {
        return $this->httpPath ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "http_path" was provided');
    }
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    public function setIncoming(?string $incoming) : void
    {
        $this->incoming = $incoming;
    }
    public function getIncoming() : ?string
    {
        return $this->incoming;
    }
    public function setOutgoing(string $outgoing) : void
    {
        $this->outgoing = $outgoing;
    }
    public function getOutgoing() : string
    {
        return $this->outgoing ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "outgoing" was provided');
    }
    public function setThrows(?string $throws) : void
    {
        $this->throws = $throws;
    }
    public function getThrows() : ?string
    {
        return $this->throws;
    }
    public function setAction(string $action) : void
    {
        $this->action = $action;
    }
    public function getAction() : string
    {
        return $this->action ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "action" was provided');
    }
    public function setCosts(?int $costs) : void
    {
        $this->costs = $costs;
    }
    public function getCosts() : ?int
    {
        return $this->costs;
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
        $record->put('active', $this->active);
        $record->put('public', $this->public);
        $record->put('stability', $this->stability);
        $record->put('description', $this->description);
        $record->put('http_method', $this->httpMethod);
        $record->put('http_path', $this->httpPath);
        $record->put('name', $this->name);
        $record->put('parameters', $this->parameters);
        $record->put('incoming', $this->incoming);
        $record->put('outgoing', $this->outgoing);
        $record->put('throws', $this->throws);
        $record->put('action', $this->action);
        $record->put('costs', $this->costs);
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
        $row->active = isset($data['active']) && is_int($data['active']) ? $data['active'] : null;
        $row->public = isset($data['public']) && is_int($data['public']) ? $data['public'] : null;
        $row->stability = isset($data['stability']) && is_int($data['stability']) ? $data['stability'] : null;
        $row->description = isset($data['description']) && is_string($data['description']) ? $data['description'] : null;
        $row->httpMethod = isset($data['http_method']) && is_string($data['http_method']) ? $data['http_method'] : null;
        $row->httpPath = isset($data['http_path']) && is_string($data['http_path']) ? $data['http_path'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->parameters = isset($data['parameters']) && is_string($data['parameters']) ? $data['parameters'] : null;
        $row->incoming = isset($data['incoming']) && is_string($data['incoming']) ? $data['incoming'] : null;
        $row->outgoing = isset($data['outgoing']) && is_string($data['outgoing']) ? $data['outgoing'] : null;
        $row->throws = isset($data['throws']) && is_string($data['throws']) ? $data['throws'] : null;
        $row->action = isset($data['action']) && is_string($data['action']) ? $data['action'] : null;
        $row->costs = isset($data['costs']) && is_int($data['costs']) ? $data['costs'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        return $row;
    }
}