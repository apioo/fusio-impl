<?php

namespace Fusio\Impl\Table\Generated;

class RoutesResponseRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setMethodId(?int $methodId) : void
    {
        $this->setProperty('method_id', $methodId);
    }
    public function getMethodId() : ?int
    {
        return $this->getProperty('method_id');
    }
    public function setCode(?int $code) : void
    {
        $this->setProperty('code', $code);
    }
    public function getCode() : ?int
    {
        return $this->getProperty('code');
    }
    public function setResponse(?string $response) : void
    {
        $this->setProperty('response', $response);
    }
    public function getResponse() : ?string
    {
        return $this->getProperty('response');
    }
}