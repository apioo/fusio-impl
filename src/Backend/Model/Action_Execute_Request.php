<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"method"})
 */
class Action_Execute_Request implements \JsonSerializable
{
    /**
     * @var string|null
     * @Pattern("GET|POST|PUT|PATCH|DELETE")
     */
    protected $method;
    /**
     * @var string|null
     */
    protected $uriFragments;
    /**
     * @var string|null
     */
    protected $parameters;
    /**
     * @var string|null
     */
    protected $headers;
    /**
     * @var Action_Execute_Request_Body|null
     */
    protected $body;
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
     * @param string|null $uriFragments
     */
    public function setUriFragments(?string $uriFragments) : void
    {
        $this->uriFragments = $uriFragments;
    }
    /**
     * @return string|null
     */
    public function getUriFragments() : ?string
    {
        return $this->uriFragments;
    }
    /**
     * @param string|null $parameters
     */
    public function setParameters(?string $parameters) : void
    {
        $this->parameters = $parameters;
    }
    /**
     * @return string|null
     */
    public function getParameters() : ?string
    {
        return $this->parameters;
    }
    /**
     * @param string|null $headers
     */
    public function setHeaders(?string $headers) : void
    {
        $this->headers = $headers;
    }
    /**
     * @return string|null
     */
    public function getHeaders() : ?string
    {
        return $this->headers;
    }
    /**
     * @param Action_Execute_Request_Body|null $body
     */
    public function setBody(?Action_Execute_Request_Body $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return Action_Execute_Request_Body|null
     */
    public function getBody() : ?Action_Execute_Request_Body
    {
        return $this->body;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('method' => $this->method, 'uriFragments' => $this->uriFragments, 'parameters' => $this->parameters, 'headers' => $this->headers, 'body' => $this->body), static function ($value) : bool {
            return $value !== null;
        });
    }
}
