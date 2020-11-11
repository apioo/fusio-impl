<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class User_JWT implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $token;
    /**
     * @var string|null
     */
    protected $expires_in;
    /**
     * @var string|null
     */
    protected $refresh_token;
    /**
     * @param string|null $token
     */
    public function setToken(?string $token) : void
    {
        $this->token = $token;
    }
    /**
     * @return string|null
     */
    public function getToken() : ?string
    {
        return $this->token;
    }
    /**
     * @param string|null $expires_in
     */
    public function setExpires_in(?string $expires_in) : void
    {
        $this->expires_in = $expires_in;
    }
    /**
     * @return string|null
     */
    public function getExpires_in() : ?string
    {
        return $this->expires_in;
    }
    /**
     * @param string|null $refresh_token
     */
    public function setRefresh_token(?string $refresh_token) : void
    {
        $this->refresh_token = $refresh_token;
    }
    /**
     * @return string|null
     */
    public function getRefresh_token() : ?string
    {
        return $this->refresh_token;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('token' => $this->token, 'expires_in' => $this->expires_in, 'refresh_token' => $this->refresh_token), static function ($value) : bool {
            return $value !== null;
        });
    }
}
