<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Dashboard_Request implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $path;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var \DateTime|null
     */
    protected $date;
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
     * @param string|null $ip
     */
    public function setIp(?string $ip) : void
    {
        $this->ip = $ip;
    }
    /**
     * @return string|null
     */
    public function getIp() : ?string
    {
        return $this->ip;
    }
    /**
     * @param \DateTime|null $date
     */
    public function setDate(?\DateTime $date) : void
    {
        $this->date = $date;
    }
    /**
     * @return \DateTime|null
     */
    public function getDate() : ?\DateTime
    {
        return $this->date;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('path' => $this->path, 'ip' => $this->ip, 'date' => $this->date), static function ($value) : bool {
            return $value !== null;
        });
    }
}
