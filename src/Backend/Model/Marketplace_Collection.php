<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Marketplace_Collection implements \JsonSerializable
{
    /**
     * @var Marketplace_Collection_Apps|null
     */
    protected $apps;
    /**
     * @param Marketplace_Collection_Apps|null $apps
     */
    public function setApps(?Marketplace_Collection_Apps $apps) : void
    {
        $this->apps = $apps;
    }
    /**
     * @return Marketplace_Collection_Apps|null
     */
    public function getApps() : ?Marketplace_Collection_Apps
    {
        return $this->apps;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('apps' => $this->apps), static function ($value) : bool {
            return $value !== null;
        });
    }
}
