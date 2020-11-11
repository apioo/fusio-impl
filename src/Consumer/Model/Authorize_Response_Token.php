<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Authorize_Response_Token implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $access_token;
    /**
     * @var string|null
     */
    protected $token_type;
    /**
     * @var string|null
     */
    protected $expires_in;
    /**
     * @var string|null
     */
    protected $scope;
    /**
     * @param string|null $access_token
     */
    public function setAccess_token(?string $access_token) : void
    {
        $this->access_token = $access_token;
    }
    /**
     * @return string|null
     */
    public function getAccess_token() : ?string
    {
        return $this->access_token;
    }
    /**
     * @param string|null $token_type
     */
    public function setToken_type(?string $token_type) : void
    {
        $this->token_type = $token_type;
    }
    /**
     * @return string|null
     */
    public function getToken_type() : ?string
    {
        return $this->token_type;
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
     * @param string|null $scope
     */
    public function setScope(?string $scope) : void
    {
        $this->scope = $scope;
    }
    /**
     * @return string|null
     */
    public function getScope() : ?string
    {
        return $this->scope;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('access_token' => $this->access_token, 'token_type' => $this->token_type, 'expires_in' => $this->expires_in, 'scope' => $this->scope), static function ($value) : bool {
            return $value !== null;
        });
    }
}
