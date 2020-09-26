<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Authorize_Meta implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $url;
    /**
     * @var array<Scope>|null
     */
    protected $scopes;
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
     * @param string|null $url
     */
    public function setUrl(?string $url) : void
    {
        $this->url = $url;
    }
    /**
     * @return string|null
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }
    /**
     * @param array<Scope>|null $scopes
     */
    public function setScopes(?array $scopes) : void
    {
        $this->scopes = $scopes;
    }
    /**
     * @return array<Scope>|null
     */
    public function getScopes() : ?array
    {
        return $this->scopes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('name' => $this->name, 'url' => $this->url, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
