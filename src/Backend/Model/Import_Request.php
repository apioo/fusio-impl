<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Import_Request implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $schema;
    /**
     * @param string|null $schema
     */
    public function setSchema(?string $schema) : void
    {
        $this->schema = $schema;
    }
    /**
     * @return string|null
     */
    public function getSchema() : ?string
    {
        return $this->schema;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('schema' => $this->schema), static function ($value) : bool {
            return $value !== null;
        });
    }
}
