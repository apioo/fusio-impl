<?php

namespace Fusio\Impl\Table\Generated;

class LogRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $categoryId = null;
    private ?int $operationId = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?string $ip = null;
    private ?string $userAgent = null;
    private ?string $method = null;
    private ?string $path = null;
    private ?string $header = null;
    private ?string $body = null;
    private ?int $executionTime = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
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
    public function setAppId(?int $appId) : void
    {
        $this->appId = $appId;
    }
    public function getAppId() : ?int
    {
        return $this->appId;
    }
    public function setUserId(?int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : ?int
    {
        return $this->userId;
    }
    public function setIp(string $ip) : void
    {
        $this->ip = $ip;
    }
    public function getIp() : string
    {
        return $this->ip ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ip" was provided');
    }
    public function setUserAgent(string $userAgent) : void
    {
        $this->userAgent = $userAgent;
    }
    public function getUserAgent() : string
    {
        return $this->userAgent ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_agent" was provided');
    }
    public function setMethod(string $method) : void
    {
        $this->method = $method;
    }
    public function getMethod() : string
    {
        return $this->method ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "method" was provided');
    }
    public function setPath(string $path) : void
    {
        $this->path = $path;
    }
    public function getPath() : string
    {
        return $this->path ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "path" was provided');
    }
    public function setHeader(string $header) : void
    {
        $this->header = $header;
    }
    public function getHeader() : string
    {
        return $this->header ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "header" was provided');
    }
    public function setBody(?string $body) : void
    {
        $this->body = $body;
    }
    public function getBody() : ?string
    {
        return $this->body;
    }
    public function setExecutionTime(?int $executionTime) : void
    {
        $this->executionTime = $executionTime;
    }
    public function getExecutionTime() : ?int
    {
        return $this->executionTime;
    }
    public function setDate(\PSX\DateTime\LocalDateTime $date) : void
    {
        $this->date = $date;
    }
    public function getDate() : \PSX\DateTime\LocalDateTime
    {
        return $this->date ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "date" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('category_id', $this->categoryId);
        $record->put('operation_id', $this->operationId);
        $record->put('app_id', $this->appId);
        $record->put('user_id', $this->userId);
        $record->put('ip', $this->ip);
        $record->put('user_agent', $this->userAgent);
        $record->put('method', $this->method);
        $record->put('path', $this->path);
        $record->put('header', $this->header);
        $record->put('body', $this->body);
        $record->put('execution_time', $this->executionTime);
        $record->put('date', $this->date);
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
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->ip = isset($data['ip']) && is_string($data['ip']) ? $data['ip'] : null;
        $row->userAgent = isset($data['user_agent']) && is_string($data['user_agent']) ? $data['user_agent'] : null;
        $row->method = isset($data['method']) && is_string($data['method']) ? $data['method'] : null;
        $row->path = isset($data['path']) && is_string($data['path']) ? $data['path'] : null;
        $row->header = isset($data['header']) && is_string($data['header']) ? $data['header'] : null;
        $row->body = isset($data['body']) && is_string($data['body']) ? $data['body'] : null;
        $row->executionTime = isset($data['execution_time']) && is_int($data['execution_time']) ? $data['execution_time'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}