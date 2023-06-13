<?php

namespace Fusio\Impl\Table\Generated;

class AppRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $userId = null;
    private ?int $status = null;
    private ?string $name = null;
    private ?string $url = null;
    private ?string $parameters = null;
    private ?string $appKey = null;
    private ?string $appSecret = null;
    private ?string $metadata = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setUserId(int $userId) : void
    {
        $this->userId = $userId;
    }
    public function getUserId() : int
    {
        return $this->userId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "user_id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }
    public function getUrl() : string
    {
        return $this->url ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "url" was provided');
    }
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    public function setAppKey(string $appKey) : void
    {
        $this->appKey = $appKey;
    }
    public function getAppKey() : string
    {
        return $this->appKey ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_key" was provided');
    }
    public function setAppSecret(string $appSecret) : void
    {
        $this->appSecret = $appSecret;
    }
    public function getAppSecret() : string
    {
        return $this->appSecret ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_secret" was provided');
    }
    public function setMetadata(?string $metadata) : void
    {
        $this->metadata = $metadata;
    }
    public function getMetadata() : ?string
    {
        return $this->metadata;
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
        $record->put('user_id', $this->userId);
        $record->put('status', $this->status);
        $record->put('name', $this->name);
        $record->put('url', $this->url);
        $record->put('parameters', $this->parameters);
        $record->put('app_key', $this->appKey);
        $record->put('app_secret', $this->appSecret);
        $record->put('metadata', $this->metadata);
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
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->url = isset($data['url']) && is_string($data['url']) ? $data['url'] : null;
        $row->parameters = isset($data['parameters']) && is_string($data['parameters']) ? $data['parameters'] : null;
        $row->appKey = isset($data['app_key']) && is_string($data['app_key']) ? $data['app_key'] : null;
        $row->appSecret = isset($data['app_secret']) && is_string($data['app_secret']) ? $data['app_secret'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}