<?php

declare(strict_types = 1);

namespace Fusio\Impl\Export\Model;


class Export_Debug implements \JsonSerializable
{
    /**
     * @var Export_Debug_Headers|null
     */
    protected $headers;
    /**
     * @var Export_Debug_Parameters|null
     */
    protected $parameters;
    /**
     * @var Export_Debug_Body|null
     */
    protected $body;
    /**
     * @param Export_Debug_Headers|null $headers
     */
    public function setHeaders(?Export_Debug_Headers $headers) : void
    {
        $this->headers = $headers;
    }
    /**
     * @return Export_Debug_Headers|null
     */
    public function getHeaders() : ?Export_Debug_Headers
    {
        return $this->headers;
    }
    /**
     * @param Export_Debug_Parameters|null $parameters
     */
    public function setParameters(?Export_Debug_Parameters $parameters) : void
    {
        $this->parameters = $parameters;
    }
    /**
     * @return Export_Debug_Parameters|null
     */
    public function getParameters() : ?Export_Debug_Parameters
    {
        return $this->parameters;
    }
    /**
     * @param Export_Debug_Body|null $body
     */
    public function setBody(?Export_Debug_Body $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return Export_Debug_Body|null
     */
    public function getBody() : ?Export_Debug_Body
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
