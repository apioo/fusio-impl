<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Action implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,255}$")
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $class;
    /**
     * @var string|null
     */
    protected $engine;
    /**
     * @var Action_Config|null
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
     * @param int|null $status
     */
    public function setStatus(?int $status) : void
    {
        $this->status = $status;
    }
    /**
     * @return int|null
     */
    public function getStatus() : ?int
    {
        return $this->status;
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
     * @param string|null $class
     */
    public function setClass(?string $class) : void
    {
        $this->class = $class;
    }
    /**
     * @return string|null
     */
    public function getClass() : ?string
    {
        return $this->class;
    }
    /**
     * @param string|null $engine
     */
    public function setEngine(?string $engine) : void
    {
        $this->engine = $engine;
    }
    /**
     * @return string|null
     */
    public function getEngine() : ?string
    {
        return $this->engine;
    }
    /**
     * @param Action_Config|null $config
     */
    public function setConfig(?Action_Config $config) : void
    {
        $this->config = $config;
    }
    /**
     * @return Action_Config|null
     */
    public function getConfig() : ?Action_Config
    {
        return $this->config;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'name' => $this->name, 'class' => $this->class, 'engine' => $this->engine, 'config' => $this->config), static function ($value) : bool {
            return $value !== null;
        });
    }
}
