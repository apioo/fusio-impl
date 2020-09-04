<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Form_Element_Select_Option implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $key;
    /**
     * @var string|null
     */
    protected $value;
    /**
     * @param string|null $key
     */
    public function setKey(?string $key) : void
    {
        $this->key = $key;
    }
    /**
     * @return string|null
     */
    public function getKey() : ?string
    {
        return $this->key;
    }
    /**
     * @param string|null $value
     */
    public function setValue(?string $value) : void
    {
        $this->value = $value;
    }
    /**
     * @return string|null
     */
    public function getValue() : ?string
    {
        return $this->value;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('key' => $this->key, 'value' => $this->value), static function ($value) : bool {
            return $value !== null;
        });
    }
}
