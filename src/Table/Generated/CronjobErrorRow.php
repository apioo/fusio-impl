<?php

namespace Fusio\Impl\Table\Generated;

class CronjobErrorRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setCronjobId(?int $cronjobId) : void
    {
        $this->setProperty('cronjob_id', $cronjobId);
    }
    public function getCronjobId() : ?int
    {
        return $this->getProperty('cronjob_id');
    }
    public function setMessage(?string $message) : void
    {
        $this->setProperty('message', $message);
    }
    public function getMessage() : ?string
    {
        return $this->getProperty('message');
    }
    public function setTrace(?string $trace) : void
    {
        $this->setProperty('trace', $trace);
    }
    public function getTrace() : ?string
    {
        return $this->getProperty('trace');
    }
    public function setFile(?string $file) : void
    {
        $this->setProperty('file', $file);
    }
    public function getFile() : ?string
    {
        return $this->getProperty('file');
    }
    public function setLine(?int $line) : void
    {
        $this->setProperty('line', $line);
    }
    public function getLine() : ?int
    {
        return $this->getProperty('line');
    }
}