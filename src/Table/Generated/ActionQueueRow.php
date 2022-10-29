<?php

namespace Fusio\Impl\Table\Generated;

class ActionQueueRow extends \PSX\Record\Record
{
    public function setId(int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : int
    {
        return $this->getProperty('id');
    }
    public function setAction(string $action) : void
    {
        $this->setProperty('action', $action);
    }
    public function getAction() : string
    {
        return $this->getProperty('action');
    }
    public function setRequest(string $request) : void
    {
        $this->setProperty('request', $request);
    }
    public function getRequest() : string
    {
        return $this->getProperty('request');
    }
    public function setContext(string $context) : void
    {
        $this->setProperty('context', $context);
    }
    public function getContext() : string
    {
        return $this->getProperty('context');
    }
    public function setDate(\DateTime $date) : void
    {
        $this->setProperty('date', $date);
    }
    public function getDate() : \DateTime
    {
        return $this->getProperty('date');
    }
}