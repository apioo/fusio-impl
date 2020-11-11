<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Log implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var string|null
     */
    protected $ip;
    /**
     * @var string|null
     */
    protected $userAgent;
    /**
     * @var string|null
     */
    protected $method;
    /**
     * @var string|null
     */
    protected $path;
    /**
     * @var string|null
     */
    protected $header;
    /**
     * @var string|null
     */
    protected $body;
    /**
     * @var \DateTime|null
     */
    protected $date;
    /**
     * @var array<Log_Error>|null
     */
    protected $errors;
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
     * @param string|null $userAgent
     */
    public function setUserAgent(?string $userAgent) : void
    {
        $this->userAgent = $userAgent;
    }
    /**
     * @return string|null
     */
    public function getUserAgent() : ?string
    {
        return $this->userAgent;
    }
    /**
     * @param string|null $method
     */
    public function setMethod(?string $method) : void
    {
        $this->method = $method;
    }
    /**
     * @return string|null
     */
    public function getMethod() : ?string
    {
        return $this->method;
    }
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
     * @param string|null $header
     */
    public function setHeader(?string $header) : void
    {
        $this->header = $header;
    }
    /**
     * @return string|null
     */
    public function getHeader() : ?string
    {
        return $this->header;
    }
    /**
     * @param string|null $body
     */
    public function setBody(?string $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return string|null
     */
    public function getBody() : ?string
    {
        return $this->body;
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
    /**
     * @param array<Log_Error>|null $errors
     */
    public function setErrors(?array $errors) : void
    {
        $this->errors = $errors;
    }
    /**
     * @return array<Log_Error>|null
     */
    public function getErrors() : ?array
    {
        return $this->errors;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'ip' => $this->ip, 'userAgent' => $this->userAgent, 'method' => $this->method, 'path' => $this->path, 'header' => $this->header, 'body' => $this->body, 'date' => $this->date, 'errors' => $this->errors), static function ($value) : bool {
            return $value !== null;
        });
    }
}
