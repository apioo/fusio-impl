<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Grant implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var App|null
     */
    protected $app;
    /**
     * @var \DateTime|null
     */
    protected $createDate;
    /**
     * @param int|null $id
     */
    public function setId(?int $id) : void
    {
        $this->id = $id;
    }
    /**
     * @return int|null
     */
    public function getId() : ?int
    {
        return $this->id;
    }
    /**
     * @param App|null $app
     */
    public function setApp(?App $app) : void
    {
        $this->app = $app;
    }
    /**
     * @return App|null
     */
    public function getApp() : ?App
    {
        return $this->app;
    }
    /**
     * @param \DateTime|null $createDate
     */
    public function setCreateDate(?\DateTime $createDate) : void
    {
        $this->createDate = $createDate;
    }
    /**
     * @return \DateTime|null
     */
    public function getCreateDate() : ?\DateTime
    {
        return $this->createDate;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'app' => $this->app, 'createDate' => $this->createDate), static function ($value) : bool {
            return $value !== null;
        });
    }
}
