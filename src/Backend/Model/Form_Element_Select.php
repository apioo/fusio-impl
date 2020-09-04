<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Form_Element_Select extends Form_Element implements \JsonSerializable
{
    /**
     * @var array<Form_Element_Select_Option>|null
     */
    protected $options;
    /**
     * @param array<Form_Element_Select_Option>|null $options
     */
    public function setOptions(?array $options) : void
    {
        $this->options = $options;
    }
    /**
     * @return array<Form_Element_Select_Option>|null
     */
    public function getOptions() : ?array
    {
        return $this->options;
    }
    public function jsonSerialize()
    {
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('options' => $this->options), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
