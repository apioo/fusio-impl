<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class Debug implements \JsonSerializable
{
    /**
     * @var Debug_Headers|null
     */
    protected $headers;
    /**
     * @var Debug_Parameters|null
     */
    protected $parameters;
    /**
     * @var Debug_Body|null
     */
    protected $body;
    /**
     * @param Debug_Headers|null $headers
     */
    public function setHeaders(?Debug_Headers $headers) : void
    {
        $this->headers = $headers;
    }
    /**
     * @return Debug_Headers|null
     */
    public function getHeaders() : ?Debug_Headers
    {
        return $this->headers;
    }
    /**
     * @param Debug_Parameters|null $parameters
     */
    public function setParameters(?Debug_Parameters $parameters) : void
    {
        $this->parameters = $parameters;
    }
    /**
     * @return Debug_Parameters|null
     */
    public function getParameters() : ?Debug_Parameters
    {
        return $this->parameters;
    }
    /**
     * @param Debug_Body|null $body
     */
    public function setBody(?Debug_Body $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return Debug_Body|null
     */
    public function getBody() : ?Debug_Body
    {
        return $this->body;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('headers' => $this->headers, 'parameters' => $this->parameters, 'body' => $this->body), static function ($value) : bool {
            return $value !== null;
        });
    }
}
