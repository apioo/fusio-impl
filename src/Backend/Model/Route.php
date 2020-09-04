<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $priority;
    /**
     * @var string|null
     */
    protected $path;
    /**
     * @var string|null
     */
    protected $controller;
    /**
     * @var array<string>|null
     */
    protected $scopes;
    /**
     * @var array<Route_Version>|null
     */
    protected $config;
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
     * @param int|null $priority
     */
    public function setPriority(?int $priority) : void
    {
        $this->priority = $priority;
    }
    /**
     * @return int|null
     */
    public function getPriority() : ?int
    {
        return $this->priority;
    }
    /**
     * @param string|null $path
     */
    public function setPath(?string $path) : void
    {
        $this->path = $path;
    }
    /**
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
    }
    /**
     * @param string|null $controller
     */
    public function setController(?string $controller) : void
    {
        $this->controller = $controller;
    }
    /**
     * @return string|null
     */
    public function getController() : ?string
    {
        return $this->controller;
    }
    /**
     * @param array<string>|null $scopes
     */
    public function setScopes(?array $scopes) : void
    {
        $this->scopes = $scopes;
    }
    /**
     * @return array<string>|null
     */
    public function getScopes() : ?array
    {
        return $this->scopes;
    }
    /**
     * @param array<Route_Version>|null $config
     */
    public function setConfig(?array $config) : void
    {
        $this->config = $config;
    }
    /**
     * @return array<Route_Version>|null
     */
    public function getConfig() : ?array
    {
        return $this->config;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'priority' => $this->priority, 'path' => $this->path, 'controller' => $this->controller, 'scopes' => $this->scopes, 'config' => $this->config), static function ($value) : bool {
            return $value !== null;
        });
    }
}
