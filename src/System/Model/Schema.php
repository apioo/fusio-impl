<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class Schema implements \JsonSerializable
{
    /**
     * @var Schema_TypeSchema|null
     */
    protected $schema;
    /**
     * @var Schema_Form|null
     */
    protected $form;
    /**
     * @param Schema_TypeSchema|null $schema
     */
    public function setSchema(?Schema_TypeSchema $schema) : void
    {
        $this->schema = $schema;
    }
    /**
     * @return Schema_TypeSchema|null
     */
    public function getSchema() : ?Schema_TypeSchema
    {
        return $this->schema;
    }
    /**
     * @param Schema_Form|null $form
     */
    public function setForm(?Schema_Form $form) : void
    {
        $this->form = $form;
    }
    /**
     * @return Schema_Form|null
     */
    public function getForm() : ?Schema_Form
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
