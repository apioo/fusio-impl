<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Plan implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var float|null
     */
    protected $price;
    /**
     * @var int|null
     */
    protected $points;
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
     * @param float|null $price
     */
    public function setPrice(?float $price) : void
    {
        $this->price = $price;
    }
    /**
     * @return float|null
     */
    public function getPrice() : ?float
    {
        return $this->price;
    }
    /**
     * @param int|null $points
     */
    public function setPoints(?int $points) : void
    {
        $this->points = $points;
    }
    /**
     * @return int|null
     */
    public function getPoints() : ?int
    {
        return $this->points;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'name' => $this->name, 'description' => $this->description, 'price' => $this->price, 'points' => $this->points), static function ($value) : bool {
            return $value !== null;
        });
    }
}
