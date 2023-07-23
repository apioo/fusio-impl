<?php

namespace Fusio\Impl\Table\Generated;

class UserRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $roleId = null;
    private ?int $planId = null;
    private ?int $identityId = null;
    private ?int $status = null;
    private ?string $remoteId = null;
    private ?string $externalId = null;
    private ?string $name = null;
    private ?string $email = null;
    private ?string $password = null;
    private ?int $points = null;
    private ?string $token = null;
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
    public function setRoleId(int $roleId) : void
    {
        $this->roleId = $roleId;
    }
    public function getRoleId() : int
    {
        return $this->roleId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "role_id" was provided');
    }
    public function setPlanId(?int $planId) : void
    {
        $this->planId = $planId;
    }
    public function getPlanId() : ?int
    {
        return $this->planId;
    }
    public function setIdentityId(?int $identityId) : void
    {
        $this->identityId = $identityId;
    }
    public function getIdentityId() : ?int
    {
        return $this->identityId;
    }
    public function getProvider() : ?int
    {
        return $this->identityId;
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setRemoteId(?string $remoteId) : void
    {
        $this->remoteId = $remoteId;
    }
    public function getRemoteId() : ?string
    {
        return $this->remoteId;
    }
    public function setExternalId(?string $externalId) : void
    {
        $this->externalId = $externalId;
    }
    public function getExternalId() : ?string
    {
        return $this->externalId;
    }
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setEmail(?string $email) : void
    {
        $this->email = $email;
    }
    public function getEmail() : ?string
    {
        return $this->email;
    }
    public function setPassword(?string $password) : void
    {
        $this->password = $password;
    }
    public function getPassword() : ?string
    {
        return $this->password;
    }
    public function setPoints(?int $points) : void
    {
        $this->points = $points;
    }
    public function getPoints() : ?int
    {
        return $this->points;
    }
    public function setToken(?string $token) : void
    {
        $this->token = $token;
    }
    public function getToken() : ?string
    {
        return $this->token;
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
        $record->put('role_id', $this->roleId);
        $record->put('plan_id', $this->planId);
        $record->put('identity_id', $this->identityId);
        $record->put('status', $this->status);
        $record->put('remote_id', $this->remoteId);
        $record->put('external_id', $this->externalId);
        $record->put('name', $this->name);
        $record->put('email', $this->email);
        $record->put('password', $this->password);
        $record->put('points', $this->points);
        $record->put('token', $this->token);
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
        $row->roleId = isset($data['role_id']) && is_int($data['role_id']) ? $data['role_id'] : null;
        $row->planId = isset($data['plan_id']) && is_int($data['plan_id']) ? $data['plan_id'] : null;
        $row->identityId = isset($data['identity_id']) && is_int($data['identity_id']) ? $data['identity_id'] : null;
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->remoteId = isset($data['remote_id']) && is_string($data['remote_id']) ? $data['remote_id'] : null;
        $row->externalId = isset($data['external_id']) && is_string($data['external_id']) ? $data['external_id'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->email = isset($data['email']) && is_string($data['email']) ? $data['email'] : null;
        $row->password = isset($data['password']) && is_string($data['password']) ? $data['password'] : null;
        $row->points = isset($data['points']) && is_int($data['points']) ? $data['points'] : null;
        $row->token = isset($data['token']) && is_string($data['token']) ? $data['token'] : null;
        $row->metadata = isset($data['metadata']) && is_string($data['metadata']) ? $data['metadata'] : null;
        $row->date = isset($data['date']) && $data['date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['date']) : null;
        return $row;
    }
}