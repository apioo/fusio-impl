<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route_Version implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $version;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var Route_Methods|null
     */
    protected $methods;
    /**
     * @param int|null $version
     */
    public function setVersion(?int $version) : void
    {
        $this->version = $version;
    }
    /**
     * @return int|null
     */
    public function getVersion() : ?int
    {
        return $this->version;
    }
    /**
     * @param int|null $status
     */
    public function setStatus(?int $status) : void
    {
        $this->status = $status;
    }
    /**
     * @return int|null
     */
    public function getStatus() : ?int
    {
        return $this->status;
    }
    /**
     * @param Route_Methods|null $methods
     */
    public function setMethods(?Route_Methods $methods) : void
    {
        $this->methods = $methods;
    }
    /**
     * @return Route_Methods|null
     */
    public function getMethods() : ?Route_Methods
    {
        return $this->methods;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('version' => $this->version, 'status' => $this->status, 'methods' => $this->methods), static function ($value) : bool {
            return $value !== null;
        });
    }
}
