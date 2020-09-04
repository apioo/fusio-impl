<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Sdk_Generate implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $format;
    /**
     * @var string|null
     */
    protected $config;
    /**
     * @param string|null $format
     */
    public function setFormat(?string $format) : void
    {
        $this->format = $format;
    }
    /**
     * @return string|null
     */
    public function getFormat() : ?string
    {
        return $this->format;
    }
    /**
     * @param string|null $config
     */
    public function setConfig(?string $config) : void
    {
        $this->config = $config;
    }
    /**
     * @return string|null
     */
    public function getConfig() : ?string
    {
        return $this->config;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('format' => $this->format, 'config' => $this->config), static function ($value) : bool {
            return $value !== null;
        });
    }
}
