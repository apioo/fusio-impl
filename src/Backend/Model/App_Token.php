<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class App_Token implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $token;
    /**
     * @var string|null
     */
    protected $scope;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var \DateTime|null
     */
    protected $expire;
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
     * @param string|null $token
     */
    public function setToken(?string $token) : void
    {
        $this->token = $token;
    }
    /**
     * @return string|null
     */
    public function getToken() : ?string
    {
        return $this->token;
    }
    /**
     * @param string|null $scope
     */
    public function setScope(?string $scope) : void
    {
        $this->scope = $scope;
    }
    /**
     * @return string|null
     */
    public function getScope() : ?string
    {
        return $this->scope;
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
     * @param \DateTime|null $expire
     */
    public function setExpire(?\DateTime $expire) : void
    {
        $this->expire = $expire;
    }
    /**
     * @return \DateTime|null
     */
    public function getExpire() : ?\DateTime
    {
        return $this->expire;
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
        return (object) array_filter(array('id' => $this->id, 'token' => $this->token, 'scope' => $this->scope, 'ip' => $this->ip, 'expire' => $this->expire, 'date' => $this->date), static function ($value) : bool {
            return $value !== null;
        });
    }
}
