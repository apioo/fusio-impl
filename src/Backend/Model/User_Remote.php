<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class User_Remote implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $provider;
    /**
     * @var string|null
     */
    protected $remoteId;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_\.]{3,32}$")
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $email;
    /**
     * @var array<string>|null
     */
    protected $scopes;
    /**
     * @param int|null $id
     */
    public function setId(?int $id) : void
    {
        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }
    /**
     * @param string|null $provider
     */
    public function setProvider(?string $provider) : void
    {
        $this->provider = $provider;
    }
    /**
     * @return string|null
     */
    public function getProvider() : ?string
    {
        return $this->provider;
    }
    /**
     * @param string|null $remoteId
     */
    public function setRemoteId(?string $remoteId) : void
    {
        $this->remoteId = $remoteId;
    }
    /**
     * @return string|null
     */
    public function getRemoteId() : ?string
    {
        return $this->remoteId;
    }
    /**
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param string|null $email
     */
    public function setEmail(?string $email) : void
    {
        $this->email = $email;
    }
    /**
     * @return string|null
     */
    public function getEmail() : ?string
    {
        return $this->email;
    }
    /**
     * @param array<string>|null $scopes
     */
    public function setScopes(?array $scopes) : void
    {
        $this->scopes = $scopes;
    }
    /**
     * @return array<string>|null
     */
    public function getScopes() : ?array
    {
        return $this->scopes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'provider' => $this->provider, 'remoteId' => $this->remoteId, 'name' => $this->name, 'email' => $this->email, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
