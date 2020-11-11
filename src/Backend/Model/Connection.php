<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Connection implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
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
     * @var Connection_Config|null
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
     * @param Connection_Config|null $config
     */
    public function setConfig(?Connection_Config $config) : void
    {
        $this->config = $config;
    }
    /**
     * @return Connection_Config|null
     */
    public function getConfig() : ?Connection_Config
    {
        return $this->config;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'name' => $this->name, 'class' => $this->class, 'config' => $this->config), static function ($value) : bool {
            return $value !== null;
        });
    }
}
