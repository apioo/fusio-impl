<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Dashboard_User implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $status;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var \DateTime|null
     */
    protected $date;
    /**
     * @param string|null $status
     */
    public function setStatus(?string $status) : void
    {
        $this->status = $status;
    }
    /**
     * @return string|null
     */
    public function getStatus() : ?string
    {
        return $this->status;
    }
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
        return (object) array_filter(array('status' => $this->status, 'name' => $this->name, 'date' => $this->date), static function ($value) : bool {
            return $value !== null;
        });
    }
}
