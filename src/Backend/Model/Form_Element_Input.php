<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Form_Element_Input extends Form_Element implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $type;
    /**
     * @param string|null $type
     */
    public function setType(?string $type) : void
    {
        $this->type = $type;
    }
    /**
     * @return string|null
     */
    public function getType() : ?string
    {
        return $this->type;
    }
    public function jsonSerialize()
    {
        return (object) array_merge((array) parent::jsonSerialize(), array_filter(array('type' => $this->type), static function ($value) : bool {
            return $value !== null;
        }));
    }
}
