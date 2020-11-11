<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;


class App implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $userId;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     * @Pattern("^[A-z0-9\-\_]{3,64}$")
     */
    protected $name;
    /**
     * @var string|null
     * @MinLength(8)
     */
    protected $url;
    /**
     * @var string|null
     */
    protected $appKey;
    /**
     * @var string|null
     */
    protected $appSecret;
    /**
     * @var string|null
     */
    protected $date;
    /**
     * @var array<string>|null
     */
    protected $scopes;
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
     * @param string|null $url
     */
    public function setUrl(?string $url) : void
    {
        $this->url = $url;
    }
    /**
     * @return string|null
     */
    public function getUrl() : ?string
    {
        return $this->url;
    }
    /**
     * @param string|null $appKey
     */
    public function setAppKey(?string $appKey) : void
    {
        $this->appKey = $appKey;
    }
    /**
     * @return string|null
     */
    public function getAppKey() : ?string
    {
        return $this->appKey;
    }
    /**
     * @param string|null $appSecret
     */
    public function setAppSecret(?string $appSecret) : void
    {
        $this->appSecret = $appSecret;
    }
    /**
     * @return string|null
     */
    public function getAppSecret() : ?string
    {
        return $this->appSecret;
    }
    /**
     * @param string|null $date
     */
    public function setDate(?string $date) : void
    {
        $this->date = $date;
    }
    /**
     * @return string|null
     */
    public function getDate() : ?string
    {
        return $this->date;
    }
    /**
     * @param array<string>|null $scopes
     */
    public function setScopes(?array $scopes) : void
    {
        $this->scopes = $scopes;
    }
    /**
     * @return array<string>|null
     */
    public function getScopes() : ?array
    {
        return $this->scopes;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'userId' => $this->userId, 'status' => $this->status, 'name' => $this->name, 'url' => $this->url, 'appKey' => $this->appKey, 'appSecret' => $this->appSecret, 'date' => $this->date, 'scopes' => $this->scopes), static function ($value) : bool {
            return $value !== null;
        });
    }
}
