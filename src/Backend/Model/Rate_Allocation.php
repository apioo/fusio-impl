<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Rate_Allocation implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $routeId;
    /**
     * @var int|null
     */
    protected $appId;
    /**
     * @var bool|null
     */
    protected $authenticated;
    /**
     * @var string|null
     */
    protected $parameters;
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
     * @param int|null $routeId
     */
    public function setRouteId(?int $routeId) : void
    {
        $this->routeId = $routeId;
    }
    /**
     * @return int|null
     */
    public function getRouteId() : ?int
    {
        return $this->routeId;
    }
    /**
     * @param int|null $appId
     */
    public function setAppId(?int $appId) : void
    {
        $this->appId = $appId;
    }
    /**
     * @return int|null
     */
    public function getAppId() : ?int
    {
        return $this->appId;
    }
    /**
     * @param bool|null $authenticated
     */
    public function setAuthenticated(?bool $authenticated) : void
    {
        $this->authenticated = $authenticated;
    }
    /**
     * @return bool|null
     */
    public function getAuthenticated() : ?bool
    {
        return $this->authenticated;
    }
    /**
     * @param string|null $parameters
     */
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    /**
     * @return string|null
     */
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'routeId' => $this->routeId, 'appId' => $this->appId, 'authenticated' => $this->authenticated, 'parameters' => $this->parameters), static function ($value) : bool {
            return $value !== null;
        });
    }
}
