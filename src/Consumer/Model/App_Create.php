<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"name", "url", "scopes"})
 */
class App_Create implements \JsonSerializable
{
    /**
     * @var string|null
     * @Pattern("^[A-z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var string|null
     * @MinLength(8)
     */
    protected $url;
    /**
     * @var array<string>|null
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
        return (object) array_filter(array('name' => $this->name, 'url' => $this->url, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
