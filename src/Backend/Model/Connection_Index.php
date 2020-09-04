<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Connection_Index implements \JsonSerializable
{
    /**
     * @var array<Connection>|null
     */
    protected $connections;
    /**
     * @param array<Connection>|null $connections
     */
    public function setConnections(?array $connections) : void
    {
        $this->connections = $connections;
    }
    /**
     * @return array<Connection>|null
     */
    public function getConnections() : ?array
    {
        return $this->connections;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('connections' => $this->connections), static function ($value) : bool {
            return $value !== null;
        });
    }
}
