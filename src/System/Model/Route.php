<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class Route implements \JsonSerializable
{
    /**
     * @var Route_Path|null
     */
    protected $routes;
    /**
     * @param Route_Path|null $routes
     */
    public function setRoutes(?Route_Path $routes) : void
    {
        $this->routes = $routes;
    }
    /**
     * @return Route_Path|null
     */
    public function getRoutes() : ?Route_Path
    {
        return $this->routes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('routes' => $this->routes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
