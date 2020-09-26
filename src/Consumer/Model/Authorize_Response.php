<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Authorize_Response implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $type;
    /**
     * @var Authorize_Response_Token|null
     */
    protected $token;
    /**
     * @var string|null
     */
    protected $code;
    /**
     * @var string|null
     */
    protected $redirectUri;
    /**
     * @param string|null $type
     */
    public function setType(?string $type) : void
    {
        $this->type = $type;
    }
    /**
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }
    /**
     * @param Authorize_Response_Token|null $token
     */
    public function setToken(?Authorize_Response_Token $token) : void
    {
        $this->token = $token;
    }
    /**
     * @return Authorize_Response_Token|null
     */
    public function getToken() : ?Authorize_Response_Token
    {
        return $this->token;
    }
    /**
     * @param string|null $code
     */
    public function setCode(?string $code) : void
    {
        $this->code = $code;
    }
    /**
     * @return string|null
     */
    public function getCode() : ?string
    {
        return $this->code;
    }
    /**
     * @param string|null $redirectUri
     */
    public function setRedirectUri(?string $redirectUri) : void
    {
        $this->redirectUri = $redirectUri;
    }
    /**
     * @return string|null
     */
    public function getRedirectUri() : ?string
    {
        return $this->redirectUri;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('type' => $this->type, 'token' => $this->token, 'code' => $this->code, 'redirectUri' => $this->redirectUri), static function ($value) : bool {
            return $value !== null;
        });
    }
}
