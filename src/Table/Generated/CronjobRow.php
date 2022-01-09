<?php

namespace Fusio\Impl\Table\Generated;

class CronjobRow extends \PSX\Record\Record
{
    public function setId(?int $id) : void
    {
        $this->setProperty('id', $id);
    }
    public function getId() : ?int
    {
        return $this->getProperty('id');
    }
    public function setCategoryId(?int $categoryId) : void
    {
        $this->setProperty('category_id', $categoryId);
    }
    public function getCategoryId() : ?int
    {
        return $this->getProperty('category_id');
    }
    public function setStatus(?int $status) : void
    {
        $this->setProperty('status', $status);
    }
    public function getStatus() : ?int
    {
        return $this->getProperty('status');
    }
    public function setName(?string $name) : void
    {
        $this->setProperty('name', $name);
    }
    public function getName() : ?string
    {
        return $this->getProperty('name');
    }
    public function setCron(?string $cron) : void
    {
        $this->setProperty('cron', $cron);
    }
    public function getCron() : ?string
    {
        return $this->getProperty('cron');
    }
    public function setAction(?string $action) : void
    {
        $this->setProperty('action', $action);
    }
    public function getAction() : ?string
    {
        return $this->getProperty('action');
    }
    public function setExecuteDate(?\DateTime $executeDate) : void
    {
        $this->setProperty('execute_date', $executeDate);
    }
    public function getExecuteDate() : ?\DateTime
    {
        return $this->getProperty('execute_date');
    }
    public function setExitCode(?int $exitCode) : void
    {
        $this->setProperty('exit_code', $exitCode);
    }
    public function getExitCode() : ?int
    {
        return $this->getProperty('exit_code');
    }
}