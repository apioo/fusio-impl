<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Form_Element_TextArea extends Form_Element implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $mode;
    /**
     * @param string|null $mode
     */
    public function setMode(?string $mode) : void
    {
        $this->mode = $mode;
    }
    /**
     * @return string|null
     */
    public function getMode() : ?string
    {
        return $this->mode;
    }
    public function jsonSerialize()
    {
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('mode' => $this->mode), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
