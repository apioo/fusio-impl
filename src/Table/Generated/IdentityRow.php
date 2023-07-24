<?php

namespace Fusio\Impl\Table\Generated;

class IdentityRow implements \JsonSerializable, \PSX\Record\RecordableInterface
{
    private ?int $id = null;
    private ?int $status = null;
    private ?int $appId = null;
    private ?int $roleId = null;
    private ?string $name = null;
    private ?string $icon = null;
    private ?string $class = null;
    private ?string $config = null;
    private ?bool $allowCreate = null;
    private ?\PSX\DateTime\LocalDateTime $insertDate = null;
    public function setId(int $id) : void
    {
        $this->id = $id;
    }
    public function getId() : int
    {
        return $this->id ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "id" was provided');
    }
    public function setStatus(int $status) : void
    {
        $this->status = $status;
    }
    public function getStatus() : int
    {
        return $this->status ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "status" was provided');
    }
    public function setAppId(int $appId) : void
    {
        $this->appId = $appId;
    }
    public function getAppId() : int
    {
        return $this->appId ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "app_id" was provided');
    }
    public function setRoleId(?int $roleId) : void
    {
        $this->roleId = $roleId;
    }
    public function getRoleId() : ?int
    {
        return $this->roleId;
    }
    public function setName(string $name) : void
    {
        $this->name = $name;
    }
    public function getName() : string
    {
        return $this->name ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "name" was provided');
    }
    public function setIcon(string $icon) : void
    {
        $this->icon = $icon;
    }
    public function getIcon() : string
    {
        return $this->icon ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "icon" was provided');
    }
    public function setClass(string $class) : void
    {
        $this->class = $class;
    }
    public function getClass() : string
    {
        return $this->class ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "class" was provided');
    }
    public function setConfig(?string $config) : void
    {
        $this->config = $config;
    }
    public function getConfig() : ?string
    {
        return $this->config;
    }
    public function setAllowCreate(bool $allowCreate) : void
    {
        $this->allowCreate = $allowCreate;
    }
    public function getAllowCreate() : bool
    {
        return $this->allowCreate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "allow_create" was provided');
    }
    public function setInsertDate(\PSX\DateTime\LocalDateTime $insertDate) : void
    {
        $this->insertDate = $insertDate;
    }
    public function getInsertDate() : \PSX\DateTime\LocalDateTime
    {
        return $this->insertDate ?? throw new \PSX\Sql\Exception\NoValueAvailable('No value for required column "insert_date" was provided');
    }
    public function toRecord() : \PSX\Record\RecordInterface
    {
        /** @var \PSX\Record\Record<mixed> $record */
        $record = new \PSX\Record\Record();
        $record->put('id', $this->id);
        $record->put('status', $this->status);
        $record->put('app_id', $this->appId);
        $record->put('role_id', $this->roleId);
        $record->put('name', $this->name);
        $record->put('icon', $this->icon);
        $record->put('class', $this->class);
        $record->put('config', $this->config);
        $record->put('allow_create', $this->allowCreate);
        $record->put('insert_date', $this->insertDate);
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
        $row->status = isset($data['status']) && is_int($data['status']) ? $data['status'] : null;
        $row->appId = isset($data['app_id']) && is_int($data['app_id']) ? $data['app_id'] : null;
        $row->roleId = isset($data['role_id']) && is_int($data['role_id']) ? $data['role_id'] : null;
        $row->name = isset($data['name']) && is_string($data['name']) ? $data['name'] : null;
        $row->icon = isset($data['icon']) && is_string($data['icon']) ? $data['icon'] : null;
        $row->class = isset($data['class']) && is_string($data['class']) ? $data['class'] : null;
        $row->config = isset($data['config']) && is_string($data['config']) ? $data['config'] : null;
        $row->allowCreate = isset($data['allow_create']) && is_bool($data['allow_create']) ? $data['allow_create'] : null;
        $row->insertDate = isset($data['insert_date']) && $data['insert_date'] instanceof \DateTimeInterface ? \PSX\DateTime\LocalDateTime::from($data['insert_date']) : null;
        return $row;
    }
}