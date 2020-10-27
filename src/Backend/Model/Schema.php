<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Schema implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;
    /**
     * @var int|null
     */
    protected $status;
    /**
     * @var string|null
     * @Pattern("^[a-zA-Z0-9\-\_]{3,255}$")
     */
    protected $name;
    /**
     * @var string|null
     * @Description("Contains the TypeSchema specification")
     */
    protected $source;
    /**
     * @var string|null
     * @Description("Contains a UI vocabulary to describe the UI of the schema")
     */
    protected $form;
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
     * @param string|null $name
     */
    public function setName(?string $name) : void
    {
        $this->name = $name;
    }
    /**
     * @return string|null
     */
    public function getName() : ?string
    {
        return $this->name;
    }
    /**
     * @param string|null $source
     */
    public function setSource(?string $source) : void
    {
        $this->source = $source;
    }
    /**
     * @return string|null
     */
    public function getSource() : ?string
    {
        return $this->source;
    }
    /**
     * @param string|null $form
     */
    public function setForm(?string $form) : void
    {
        $this->form = $form;
    }
    /**
     * @return string|null
     */
    public function getForm() : ?string
    {
        return $this->form;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('id' => $this->id, 'status' => $this->status, 'name' => $this->name, 'source' => $this->source, 'form' => $this->form), static function ($value) : bool {
            return $value !== null;
        });
    }
}
