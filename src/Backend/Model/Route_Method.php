<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Route_Method implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $method;
    /**
     * @var int|null
     */
    protected $version;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var bool|null
     */
    protected $active;
    /**
     * @var bool|null
     */
    protected $public;
    /**
     * @var string|null
     */
    protected $description;
    /**
     * @var string|null
     */
    protected $operationId;
    /**
     * @var int|null
     */
    protected $parameters;
    /**
     * @var int|null
     */
    protected $request;
    /**
     * @var int|null
     */
    protected $response;
    /**
     * @var Route_Method_Responses|null
     */
    protected $responses;
    /**
     * @var int|null
     */
    protected $action;
    /**
     * @var int|null
     */
    protected $costs;
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
     * @param int|null $version
     */
    public function setVersion(?int $version) : void
    {
        $this->version = $version;
    }
    /**
     * @return int|null
     */
    public function getVersion() : ?int
    {
        return $this->version;
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
     * @param bool|null $active
     */
    public function setActive(?bool $active) : void
    {
        $this->active = $active;
    }
    /**
     * @return bool|null
     */
    public function getActive() : ?bool
    {
        return $this->active;
    }
    /**
     * @param bool|null $public
     */
    public function setPublic(?bool $public) : void
    {
        $this->public = $public;
    }
    /**
     * @return bool|null
     */
    public function getPublic() : ?bool
    {
        return $this->public;
    }
    /**
     * @param string|null $description
     */
    public function setDescription(?string $description) : void
    {
        $this->description = $description;
    }
    /**
     * @return string|null
     */
    public function getDescription() : ?string
    {
        return $this->description;
    }
    /**
     * @param string|null $operationId
     */
    public function setOperationId(?string $operationId) : void
    {
        $this->operationId = $operationId;
    }
    /**
     * @return string|null
     */
    public function getOperationId() : ?string
    {
        return $this->operationId;
    }
    /**
     * @param int|null $parameters
     */
    public function setParameters(?int $parameters) : void
    {
        $this->parameters = $parameters;
    }
    /**
     * @return int|null
     */
    public function getParameters() : ?int
    {
        return $this->parameters;
    }
    /**
     * @param int|null $request
     */
    public function setRequest(?int $request) : void
    {
        $this->request = $request;
    }
    /**
     * @return int|null
     */
    public function getRequest() : ?int
    {
        return $this->request;
    }
    /**
     * @param int|null $response
     */
    public function setResponse(?int $response) : void
    {
        $this->response = $response;
    }
    /**
     * @return int|null
     */
    public function getResponse() : ?int
    {
        return $this->response;
    }
    /**
     * @param Route_Method_Responses|null $responses
     */
    public function setResponses(?Route_Method_Responses $responses) : void
    {
        $this->responses = $responses;
    }
    /**
     * @return Route_Method_Responses|null
     */
    public function getResponses() : ?Route_Method_Responses
    {
        return $this->responses;
    }
    /**
     * @param int|null $action
     */
    public function setAction(?int $action) : void
    {
        $this->action = $action;
    }
    /**
     * @return int|null
     */
    public function getAction() : ?int
    {
        return $this->action;
    }
    /**
     * @param int|null $costs
     */
    public function setCosts(?int $costs) : void
    {
        $this->costs = $costs;
    }
    /**
     * @return int|null
     */
    public function getCosts() : ?int
    {
        return $this->costs;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('method' => $this->method, 'version' => $this->version, 'status' => $this->status, 'active' => $this->active, 'public' => $this->public, 'description' => $this->description, 'operationId' => $this->operationId, 'parameters' => $this->parameters, 'request' => $this->request, 'response' => $this->response, 'responses' => $this->responses, 'action' => $this->action, 'costs' => $this->costs), static function ($value) : bool {
            return $value !== null;
        });
    }
}
