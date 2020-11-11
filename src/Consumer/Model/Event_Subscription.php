<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class Event_Subscription implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     */
    protected $event;
    /**
     * @var string|null
     */
    protected $endpoint;
    /**
     * @var array<Event_Subscription_Response>|null
     */
    protected $responses;
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
    /**
     * @param array<Event_Subscription_Response>|null $responses
     */
    public function setResponses(?array $responses) : void
    {
        $this->responses = $responses;
    }
    /**
     * @return array<Event_Subscription_Response>|null
     */
    public function getResponses() : ?array
    {
        return $this->responses;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'event' => $this->event, 'endpoint' => $this->endpoint, 'responses' => $this->responses), static function ($value) : bool {
            return $value !== null;
        });
    }
}
