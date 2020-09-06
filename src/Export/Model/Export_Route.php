<?php

declare(strict_types = 1);

namespace Fusio\Impl\Export\Model;


class Export_Route implements \JsonSerializable
{
    /**
     * @var Export_Route_Path|null
     */
    protected $routes;
    /**
     * @param Export_Route_Path|null $routes
     */
    public function setRoutes(?Export_Route_Path $routes) : void
    {
        $this->routes = $routes;
    }
    /**
     * @return Export_Route_Path|null
     */
    public function getRoutes() : ?Export_Route_Path
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
