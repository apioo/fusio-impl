<?php

declare(strict_types = 1);

namespace Fusio\Impl\Export\Model;


class Health_Check implements \JsonSerializable
{
    /**
     * @var bool|null
     */
    protected $healthy;
    /**
     * @var string|null
     */
    protected $error;
    /**
     * @param bool|null $healthy
     */
    public function setHealthy(?bool $healthy) : void
    {
        $this->healthy = $healthy;
    }
    /**
     * @return bool|null
     */
    public function getHealthy() : ?bool
    {
        return $this->healthy;
    }
    /**
     * @param string|null $error
     */
    public function setError(?string $error) : void
    {
        $this->error = $error;
    }
    /**
     * @return string|null
     */
    public function getError() : ?string
    {
        return $this->error;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('healthy' => $this->healthy, 'error' => $this->error), static function ($value) : bool {
            return $value !== null;
        });
    }
}
