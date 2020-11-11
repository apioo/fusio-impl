<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Action_Index_Entry implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $class;
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
    public function jsonSerialize()
    {
        return (object) array_filter(array('name' => $this->name, 'class' => $this->class), static function ($value) : bool {
            return $value !== null;
        });
    }
}
