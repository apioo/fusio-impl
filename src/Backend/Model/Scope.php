<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Scope implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var array<Scope_Route>|null
     */
    protected $routes;
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
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param string|null $description
     */
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    /**
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
    /**
     * @param array<Scope_Route>|null $routes
     */
    public function setRoutes(?array $routes) : void
    {
        $this->routes = $routes;
    }
    /**
     * @return array<Scope_Route>|null
     */
    public function getRoutes() : ?array
    {
        return $this->routes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'name' => $this->name, 'description' => $this->description, 'routes' => $this->routes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
