<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Config implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $type;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var string|float|int|bool|null
     */
    protected $value;
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
     * @param int|null $type
     */
    public function setType(?int $type) : void
    {
        $this->type = $type;
    }
    /**
     * @return int|null
     */
    public function getType() : ?int
    {
        return $this->type;
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
     * @param string|float|int|bool|null $value
     */
    public function setValue($value) : void
    {
        $this->value = $value;
    }
    /**
     * @return string|float|int|bool|null
     */
    public function getValue()
    {
        return $this->value;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'type' => $this->type, 'name' => $this->name, 'description' => $this->description, 'value' => $this->value), static function ($value) : bool {
            return $value !== null;
        });
    }
}
