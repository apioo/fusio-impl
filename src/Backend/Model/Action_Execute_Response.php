<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Action_Execute_Response implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $statusCode;
    /**
     * @var Action_Execute_Response_Headers|null
     */
    protected $headers;
    /**
     * @var Action_Execute_Response_Body|null
     */
    protected $body;
    /**
     * @param int|null $statusCode
     */
    public function setStatusCode(?int $statusCode) : void
    {
        $this->statusCode = $statusCode;
    }
    /**
     * @return int|null
     */
    public function getStatusCode() : ?int
    {
        return $this->statusCode;
    }
    /**
     * @param Action_Execute_Response_Headers|null $headers
     */
    public function setHeaders(?Action_Execute_Response_Headers $headers) : void
    {
        $this->headers = $headers;
    }
    /**
     * @return Action_Execute_Response_Headers|null
     */
    public function getHeaders() : ?Action_Execute_Response_Headers
    {
        return $this->headers;
    }
    /**
     * @param Action_Execute_Response_Body|null $body
     */
    public function setBody(?Action_Execute_Response_Body $body) : void
    {
        $this->body = $body;
    }
    /**
     * @return Action_Execute_Response_Body|null
     */
    public function getBody() : ?Action_Execute_Response_Body
    {
        return $this->body;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('statusCode' => $this->statusCode, 'headers' => $this->headers, 'body' => $this->body), static function ($value) : bool {
            return $value !== null;
        });
    }
}
