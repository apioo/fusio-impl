<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class User_Provider implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $code;
    /**
     * @var string|null
     */
    protected $clientId;
    /**
     * @var string|null
     */
    protected $redirectUri;
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
     * @param string|null $clientId
     */
    public function setClientId(?string $clientId) : void
    {
        $this->clientId = $clientId;
    }
    /**
     * @return string|null
     */
    public function getClientId() : ?string
    {
        return $this->clientId;
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
        return (object) array_filter(array('code' => $this->code, 'clientId' => $this->clientId, 'redirectUri' => $this->redirectUri), static function ($value) : bool {
            return $value !== null;
        });
    }
}
