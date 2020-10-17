<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class Rpc_Request_Call implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $jsonrpc;
    /**
     * @var string|null
     */
    protected $method;
    /**
     * @var Rpc_Request_Params|null
     */
    protected $params;
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
     * @param Rpc_Request_Params|null $params
     */
    public function setParams(?Rpc_Request_Params $params) : void
    {
        $this->params = $params;
    }
    /**
     * @return Rpc_Request_Params|null
     */
    public function getParams() : ?Rpc_Request_Params
    {
        return $this->params;
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
        return (object) array_filter(array('jsonrpc' => $this->jsonrpc, 'method' => $this->method, 'params' => $this->params, 'id' => $this->id), static function ($value) : bool {
            return $value !== null;
        });
    }
}
