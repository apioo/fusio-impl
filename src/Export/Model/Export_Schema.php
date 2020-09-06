<?php

declare(strict_types = 1);

namespace Fusio\Impl\Export\Model;


class Export_Schema implements \JsonSerializable
{
    /**
     * @var Export_Schema_TypeSchema|null
     */
    protected $schema;
    /**
     * @var Export_Schema_Form|null
     */
    protected $form;
    /**
     * @param Export_Schema_TypeSchema|null $schema
     */
    public function setSchema(?Export_Schema_TypeSchema $schema) : void
    {
        $this->schema = $schema;
    }
    /**
     * @return Export_Schema_TypeSchema|null
     */
    public function getSchema() : ?Export_Schema_TypeSchema
    {
        return $this->schema;
    }
    /**
     * @param Export_Schema_Form|null $form
     */
    public function setForm(?Export_Schema_Form $form) : void
    {
        $this->form = $form;
    }
    /**
     * @return Export_Schema_Form|null
     */
    public function getForm() : ?Export_Schema_Form
    {
        return $this->form;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('schema' => $this->schema, 'form' => $this->form), static function ($value) : bool {
            return $value !== null;
        });
    }
}
