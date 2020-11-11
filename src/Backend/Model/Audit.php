<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Audit implements \JsonSerializable
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
     * @var User|null
     */
    protected $user;
    /**
     * @var string|null
     */
    protected $event;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var string|null
     */
    protected $message;
    /**
     * @var Audit_Object|null
     */
    protected $content;
    /**
     * @var \DateTime|null
     */
    protected $date;
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
     * @param User|null $user
     */
    public function setUser(?User $user) : void
    {
        $this->user = $user;
    }
    /**
     * @return User|null
     */
    public function getUser() : ?User
    {
        return $this->user;
    }
    /**
     * @param string|null $event
     */
    public function setEvent(?string $event) : void
    {
        $this->event = $event;
    }
    /**
     * @return string|null
     */
    public function getEvent() : ?string
    {
        return $this->event;
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
     * @param string|null $message
     */
    public function setMessage(?string $message) : void
    {
        $this->message = $message;
    }
    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->message;
    }
    /**
     * @param Audit_Object|null $content
     */
    public function setContent(?Audit_Object $content) : void
    {
        $this->content = $content;
    }
    /**
     * @return Audit_Object|null
     */
    public function getContent() : ?Audit_Object
    {
        return $this->content;
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
        return (object) array_filter(array('id' => $this->id, 'app' => $this->app, 'user' => $this->user, 'event' => $this->event, 'ip' => $this->ip, 'message' => $this->message, 'content' => $this->content, 'date' => $this->date), static function ($value) : bool {
            return $value !== null;
        });
    }
}
