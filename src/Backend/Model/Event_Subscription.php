<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Event_Subscription implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $eventId;
    /**
     * @var int|null
     */
    protected $userId;
    /**
     * @var string|null
     */
    protected $endpoint;
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
     * @param int|null $eventId
     */
    public function setEventId(?int $eventId) : void
    {
        $this->eventId = $eventId;
    }
    /**
     * @return int|null
     */
    public function getEventId() : ?int
    {
        return $this->eventId;
    }
    /**
     * @param int|null $userId
     */
    public function setUserId(?int $userId) : void
    {
        $this->userId = $userId;
    }
    /**
     * @return int|null
     */
    public function getUserId() : ?int
    {
        return $this->userId;
    }
    /**
     * @param string|null $endpoint
     */
    public function setEndpoint(?string $endpoint) : void
    {
        $this->endpoint = $endpoint;
    }
    /**
     * @return string|null
     */
    public function getEndpoint() : ?string
    {
        return $this->endpoint;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'eventId' => $this->eventId, 'userId' => $this->userId, 'endpoint' => $this->endpoint), static function ($value) : bool {
            return $value !== null;
        });
    }
}
