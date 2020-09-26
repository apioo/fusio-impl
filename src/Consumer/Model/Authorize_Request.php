<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"responseType", "clientId", "scope", "allow"})
 */
class Authorize_Request implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $responseType;
    /**
     * @var string|null
     */
    protected $clientId;
    /**
     * @var string|null
     */
    protected $redirectUri;
    /**
     * @var string|null
     */
    protected $scope;
    /**
     * @var string|null
     */
    protected $state;
    /**
     * @var bool|null
     */
    protected $allow;
    /**
     * @param string|null $responseType
     */
    public function setResponseType(?string $responseType) : void
    {
        $this->responseType = $responseType;
    }
    /**
     * @return string|null
     */
    public function getResponseType() : ?string
    {
        return $this->responseType;
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
    /**
     * @param string|null $state
     */
    public function setState(?string $state) : void
    {
        $this->state = $state;
    }
    /**
     * @return string|null
     */
    public function getState() : ?string
    {
        return $this->state;
    }
    /**
     * @param bool|null $allow
     */
    public function setAllow(?bool $allow) : void
    {
        $this->allow = $allow;
    }
    /**
     * @return bool|null
     */
    public function getAllow() : ?bool
    {
        return $this->allow;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('responseType' => $this->responseType, 'clientId' => $this->clientId, 'redirectUri' => $this->redirectUri, 'scope' => $this->scope, 'state' => $this->state, 'allow' => $this->allow), static function ($value) : bool {
            return $value !== null;
        });
    }
}
