<?php

namespace Fusio\Impl\Table\Generated;

class TestRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $operationId = null;
    private ?string $tenantId = null;
    private ?int $status = null;
    private ?string $message = null;
    private ?string $response = null;
    private ?string $uriFragments = null;
    private ?string $parameters = null;
    private ?string $headers = null;
    private ?string $body = null;
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
    public function setOperationId(?int $operationId) : void
    {
        $this->operationId = $operationId;
    }
    public function getOperationId() : ?int
    {
        return $this->operationId;
    }
    public function setTenantId(?string $tenantId) : void
    {
        $this->tenantId = $tenantId;
    }
    public function getTenantId() : ?string
    {
        return $this->tenantId;
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setMessage(?string $message) : void
    {
        $this->message = $message;
    }
    public function getMessage() : ?string
    {
        return $this->message;
    }
    public function setResponse(?string $response) : void
    {
        $this->response = $response;
    }
    public function getResponse() : ?string
    {
        return $this->response;
    }
    public function setUriFragments(?string $uriFragments) : void
    {
        $this->uriFragments = $uriFragments;
    }
    public function getUriFragments() : ?string
    {
        return $this->uriFragments;
    }
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    public function setHeaders(?string $headers) : void
    {
        $this->headers = $headers;
    }
    public function getHeaders() : ?string
    {
        return $this->headers;
    }
    public function setBody(?string $body) : void
    {
        $this->body = $body;
    }
    public function getBody() : ?string
    {
        return $this->body;
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('category_id', $this->categoryId);
        $record->put('operation_id', $this->operationId);
        $record->put('tenant_id', $this->tenantId);
        $record->put('status', $this->status);
        $record->put('message', $this->message);
        $record->put('response', $this->response);
        $record->put('uri_fragments', $this->uriFragments);
        $record->put('parameters', $this->parameters);
        $record->put('headers', $this->headers);
        $record->put('body', $this->body);
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
        $row->operationId = isset($data['operation_id']) && is_int($data['operation_id']) ? $data['operation_id'] : null;
        $row->tenantId = isset($data['tenant_id']) && is_string($data['tenant_id']) ? $data['tenant_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->message = isset($data['message']) && is_string($data['message']) ? $data['message'] : null;
        $row->response = isset($data['response']) && is_string($data['response']) ? $data['response'] : null;
        $row->uriFragments = isset($data['uri_fragments']) && is_string($data['uri_fragments']) ? $data['uri_fragments'] : null;
        $row->parameters = isset($data['parameters']) && is_string($data['parameters']) ? $data['parameters'] : null;
        $row->headers = isset($data['headers']) && is_string($data['headers']) ? $data['headers'] : null;
        $row->body = isset($data['body']) && is_string($data['body']) ? $data['body'] : null;
        return $row;
    }
}