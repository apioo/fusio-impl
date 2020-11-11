<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route_Index_Providers implements \JsonSerializable
{
    /**
     * @var array<Route_Provider>|null
     */
    protected $providers;
    /**
     * @param array<Route_Provider>|null $providers
     */
    public function setProviders(?array $providers) : void
    {
        $this->providers = $providers;
    }
    /**
     * @return array<Route_Provider>|null
     */
    public function getProviders() : ?array
    {
        return $this->providers;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('providers' => $this->providers), static function ($value) : bool {
            return $value !== null;
        });
    }
}
