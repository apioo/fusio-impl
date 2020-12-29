<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Role implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $categoryId;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var array<string>|null
     */
    protected $scopes;
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
     * @param int|null $categoryId
     */
    public function setCategoryId(?int $categoryId) : void
    {
        $this->categoryId = $categoryId;
    }
    /**
     * @return int|null
     */
    public function getCategoryId() : ?int
    {
        return $this->categoryId;
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
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'categoryId' => $this->categoryId, 'name' => $this->name, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
