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
     * @var array<string>|null
     */
    protected $paymentClass;
    /**
     * @var array<string>|null
     */
    protected $userClass;
    /**
     * @var array<string>|null
     */
    protected $routesClass;
    /**
     * @var array<Connection>|null
     */
    protected $connection;
    /**
     * @var array<Schema>|null
     */
    protected $schema;
    /**
     * @var array<Action>|null
     */
    protected $action;
    /**
     * @var array<Route>|null
     */
    protected $routes;
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
     * @param array<string>|null $paymentClass
     */
    public function setPaymentClass(?array $paymentClass) : void
    {
        $this->paymentClass = $paymentClass;
    }
    /**
     * @return array<string>|null
     */
    public function getPaymentClass() : ?array
    {
        return $this->paymentClass;
    }
    /**
     * @param array<string>|null $userClass
     */
    public function setUserClass(?array $userClass) : void
    {
        $this->userClass = $userClass;
    }
    /**
     * @return array<string>|null
     */
    public function getUserClass() : ?array
    {
        return $this->userClass;
    }
    /**
     * @param array<string>|null $routesClass
     */
    public function setRoutesClass(?array $routesClass) : void
    {
        $this->routesClass = $routesClass;
    }
    /**
     * @return array<string>|null
     */
    public function getRoutesClass() : ?array
    {
        return $this->routesClass;
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
        return (object) array_filter(array('actionClass' => $this->actionClass, 'connectionClass' => $this->connectionClass, 'paymentClass' => $this->paymentClass, 'userClass' => $this->userClass, 'routesClass' => $this->routesClass, 'connection' => $this->connection, 'schema' => $this->schema, 'action' => $this->action, 'routes' => $this->routes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
