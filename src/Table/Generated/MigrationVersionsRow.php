<?php

namespace Fusio\Impl\Table\Generated;

class MigrationVersionsRow extends \PSX\Record\Record
{
    public function setVersion(string $version) : void
    {
        $this->setProperty('version', $version);
    }
    public function getVersion() : string
    {
        return $this->getProperty('version');
    }
    public function setExecutedAt(\DateTime $executedAt) : void
    {
        $this->setProperty('executed_at', $executedAt);
    }
    public function getExecutedAt() : \DateTime
    {
        return $this->getProperty('executed_at');
    }
}