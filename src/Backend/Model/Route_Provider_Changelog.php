<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route_Provider_Changelog implements \JsonSerializable
{
    /**
     * @var array<Schema>|null
     */
    protected $schemas;
    /**
     * @var array<Action>|null
     */
    protected $actions;
    /**
     * @var array<Route>|null
     */
    protected $routes;
    /**
     * @param array<Schema>|null $schemas
     */
    public function setSchemas(?array $schemas) : void
    {
        $this->schemas = $schemas;
    }
    /**
     * @return array<Schema>|null
     */
    public function getSchemas() : ?array
    {
        return $this->schemas;
    }
    /**
     * @param array<Action>|null $actions
     */
    public function setActions(?array $actions) : void
    {
        $this->actions = $actions;
    }
    /**
     * @return array<Action>|null
     */
    public function getActions() : ?array
    {
        return $this->actions;
    }
    /**
     * @param array<Route>|null $routes
     */
    public function setRoutes(?array $routes) : void
    {
        $this->routes = $routes;
    }
    /**
     * @return array<Route>|null
     */
    public function getRoutes() : ?array
    {
        return $this->routes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('schemas' => $this->schemas, 'actions' => $this->actions, 'routes' => $this->routes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
