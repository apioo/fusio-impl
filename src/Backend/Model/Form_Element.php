<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"element"})
 */
class Form_Element implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $element;
    /**
     * @var string|null
     */
    protected $name;
    /**
     * @var string|null
     */
    protected $title;
    /**
     * @var string|null
     */
    protected $help;
    /**
     * @param string|null $element
     */
    public function setElement(?string $element) : void
    {
        $this->element = $element;
    }
    /**
     * @return string|null
     */
    public function getElement() : ?string
    {
        return $this->element;
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
     * @param string|null $title
     */
    public function setTitle(?string $title) : void
    {
        $this->title = $title;
    }
    /**
     * @return string|null
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }
    /**
     * @param string|null $help
     */
    public function setHelp(?string $help) : void
    {
        $this->help = $help;
    }
    /**
     * @return string|null
     */
    public function getHelp() : ?string
    {
        return $this->help;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('element' => $this->element, 'name' => $this->name, 'title' => $this->title, 'help' => $this->help), static function ($value) : bool {
            return $value !== null;
        });
    }
}
