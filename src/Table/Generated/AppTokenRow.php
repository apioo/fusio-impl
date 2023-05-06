<?php

namespace Fusio\Impl\Table\Generated;

class AppTokenRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?int $status = null;
    private ?string $token = null;
    private ?string $refresh = null;
    private ?string $scope = null;
    private ?string $ip = null;
    private ?\PSX\DateTime\LocalDateTime $expire = null;
    private ?\PSX\DateTime\LocalDateTime $date = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setAppId(int $appId) : void
    {
        $this->appId = $appId;
    }
    public function getAppId() : int
    {
        return $this->appId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_id" was provided');
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
    public function setToken(string $token) : void
    {
        $this->token = $token;
    }
    public function getToken() : string
    {
        return $this->token ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "token" was provided');
    }
    public function setRefresh(?string $refresh) : void
    {
        $this->refresh = $refresh;
    }
    public function getRefresh() : ?string
    {
        return $this->refresh;
    }
    public function setScope(string $scope) : void
    {
        $this->scope = $scope;
    }
    public function getScope() : string
    {
        return $this->scope ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "scope" was provided');
    }
    public function setIp(string $ip) : void
    {
        $this->ip = $ip;
    }
    public function getIp() : string
    {
        return $this->ip ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "ip" was provided');
    }
    public function setExpire(?\PSX\DateTime\LocalDateTime $expire) : void
    {
        $this->expire = $expire;
    }
    public function getExpire() : ?\PSX\DateTime\LocalDateTime
    {
        return $this->expire;
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
        $record->put('app_id', $this->appId);
        $record->put('user_id', $this->userId);
        $record->put('status', $this->status);
        $record->put('token', $this->token);
        $record->put('refresh', $this->refresh);
        $record->put('scope', $this->scope);
        $record->put('ip', $this->ip);
        $record->put('expire', $this->expire);
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
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->userId = isset($data['user_id']) && is_int($data['user_id']) ? $data['user_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->token = isset($data['token']) && is_string($data['token']) ? $data['token'] : null;
        $row->refresh = isset($data['refresh']) && is_string($data['refresh']) ? $data['refresh'] : null;
        $row->scope = isset($data['scope']) && is_string($data['scope']) ? $data['scope'] : null;
        $row->ip = isset($data['ip']) && is_string($data['ip']) ? $data['ip'] : null;
        $row->expire = isset($data['expire']) && $data['expire'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['expire']) : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}