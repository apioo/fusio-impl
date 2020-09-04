<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Marketplace_Install implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $name;
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
    public function jsonSerialize()
    {
        return (object) array_filter(array('name' => $this->name), static function ($value) : bool {
            return $value !== null;
        });
    }
}
