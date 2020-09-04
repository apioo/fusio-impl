<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Scope_Route implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $routeId;
    /**
     * @var bool|null
     */
    protected $allow;
    /**
     * @var string|null
     */
    protected $methods;
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
    /**
     * @param string|null $methods
     */
    public function setMethods(?string $methods) : void
    {
        $this->methods = $methods;
    }
    /**
     * @return string|null
     */
    public function getMethods() : ?string
    {
        return $this->methods;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('routeId' => $this->routeId, 'allow' => $this->allow, 'methods' => $this->methods), static function ($value) : bool {
            return $value !== null;
        });
    }
}
