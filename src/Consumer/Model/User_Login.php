<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class User_Login implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $username;
    /**
     * @var string|null
     */
    protected $password;
    /**
     * @var array<string>|null
     */
    protected $scopes;
    /**
     * @param string|null $username
     */
    public function setUsername(?string $username) : void
    {
        $this->username = $username;
    }
    /**
     * @return string|null
     */
    public function getUsername() : ?string
    {
        return $this->username;
    }
    /**
     * @param string|null $password
     */
    public function setPassword(?string $password) : void
    {
        $this->password = $password;
    }
    /**
     * @return string|null
     */
    public function getPassword() : ?string
    {
        return $this->password;
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
        return (object) array_filter(array('username' => $this->username, 'password' => $this->password, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
