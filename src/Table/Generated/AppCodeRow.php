<?php

namespace Fusio\Impl\Table\Generated;

class AppCodeRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $appId = null;
    private ?int $userId = null;
    private ?string $code = null;
    private ?string $redirectUri = null;
    private ?string $scope = null;
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
    public function setCode(string $code) : void
    {
        $this->code = $code;
    }
    public function getCode() : string
    {
        return $this->code ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "code" was provided');
    }
    public function setRedirectUri(?string $redirectUri) : void
    {
        $this->redirectUri = $redirectUri;
    }
    public function getRedirectUri() : ?string
    {
        return $this->redirectUri;
    }
    public function setScope(string $scope) : void
    {
        $this->scope = $scope;
    }
    public function getScope() : string
    {
        return $this->scope ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "scope" was provided');
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
        $record->put('code', $this->code);
        $record->put('redirect_uri', $this->redirectUri);
        $record->put('scope', $this->scope);
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
        $row->code = isset($data['code']) && is_string($data['code']) ? $data['code'] : null;
        $row->redirectUri = isset($data['redirect_uri']) && is_string($data['redirect_uri']) ? $data['redirect_uri'] : null;
        $row->scope = isset($data['scope']) && is_string($data['scope']) ? $data['scope'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}