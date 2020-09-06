<?php

declare(strict_types = 1);

namespace Fusio\Impl\Export\Model;


class Export_Rpc_Response_Error implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $jsonrpc;
    /**
     * @var Export_Rpc_Response_Errors|null
     */
    protected $error;
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @param string|null $jsonrpc
     */
    public function setJsonrpc(?string $jsonrpc) : void
    {
        $this->jsonrpc = $jsonrpc;
    }
    /**
     * @return string|null
     */
    public function getJsonrpc() : ?string
    {
        return $this->jsonrpc;
    }
    /**
     * @param Export_Rpc_Response_Errors|null $error
     */
    public function setError(?Export_Rpc_Response_Errors $error) : void
    {
        $this->error = $error;
    }
    /**
     * @return Export_Rpc_Response_Errors|null
     */
    public function getError() : ?Export_Rpc_Response_Errors
    {
        return $this->error;
    }
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
    public function jsonSerialize()
    {
        return (object) array_filter(array('jsonrpc' => $this->jsonrpc, 'error' => $this->error, 'id' => $this->id), static function ($value) : bool {
            return $value !== null;
        });
    }
}
