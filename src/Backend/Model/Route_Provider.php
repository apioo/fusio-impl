<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route_Provider implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $path;
    /**
     * @var array<string>|null
     */
    protected $scopes;
    /**
     * @var Route_Provider_Config|null
     */
    protected $config;
    /**
     * @param string|null $path
     */
    public function setPath(?string $path) : void
    {
        $this->path = $path;
    }
    /**
     * @return string|null
     */
    public function getPath() : ?string
    {
        return $this->path;
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
    /**
     * @param Route_Provider_Config|null $config
     */
    public function setConfig(?Route_Provider_Config $config) : void
    {
        $this->config = $config;
    }
    /**
     * @return Route_Provider_Config|null
     */
    public function getConfig() : ?Route_Provider_Config
    {
        return $this->config;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('path' => $this->path, 'scopes' => $this->scopes, 'config' => $this->config), static function ($value) : bool {
            return $value !== null;
        });
    }
}
