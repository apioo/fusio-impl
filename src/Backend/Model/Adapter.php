<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Adapter implements \JsonSerializable
{
    /**
     * @var array<string>|null
     */
    protected $actionClass;
    /**
     * @var array<string>|null
     */
    protected $connectionClass;
    /**
     * @var array<Routes>|null
     */
    protected $routes;
    /**
     * @var array<Action>|null
     */
    protected $action;
    /**
     * @var array<Schema>|null
     */
    protected $schema;
    /**
     * @var array<Connection>|null
     */
    protected $connection;
    /**
     * @param array<string>|null $actionClass
     */
    public function setActionClass(?array $actionClass) : void
    {
        $this->actionClass = $actionClass;
    }
    /**
     * @return array<string>|null
     */
    public function getActionClass() : ?array
    {
        return $this->actionClass;
    }
    /**
     * @param array<string>|null $connectionClass
     */
    public function setConnectionClass(?array $connectionClass) : void
    {
        $this->connectionClass = $connectionClass;
    }
    /**
     * @return array<string>|null
     */
    public function getConnectionClass() : ?array
    {
        return $this->connectionClass;
    }
    /**
     * @param array<Routes>|null $routes
     */
    public function setRoutes(?array $routes) : void
    {
        $this->routes = $routes;
    }
    /**
     * @return array<Routes>|null
     */
    public function getRoutes() : ?array
    {
        return $this->routes;
    }
    /**
     * @param array<Action>|null $action
     */
    public function setAction(?array $action) : void
    {
        $this->action = $action;
    }
    /**
     * @return array<Action>|null
     */
    public function getAction() : ?array
    {
        return $this->action;
    }
    /**
     * @param array<Schema>|null $schema
     */
    public function setSchema(?array $schema) : void
    {
        $this->schema = $schema;
    }
    /**
     * @return array<Schema>|null
     */
    public function getSchema() : ?array
    {
        return $this->schema;
    }
    /**
     * @param array<Connection>|null $connection
     */
    public function setConnection(?array $connection) : void
    {
        $this->connection = $connection;
    }
    /**
     * @return array<Connection>|null
     */
    public function getConnection() : ?array
    {
        return $this->connection;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('actionClass' => $this->actionClass, 'connectionClass' => $this->connectionClass, 'routes' => $this->routes, 'action' => $this->action, 'schema' => $this->schema, 'connection' => $this->connection), static function ($value) : bool {
            return $value !== null;
        });
    }
}
