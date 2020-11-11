<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Schema_Preview_Response implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $preview;
    /**
     * @param string|null $preview
     */
    public function setPreview(?string $preview) : void
    {
        $this->preview = $preview;
    }
    /**
     * @return string|null
     */
    public function getPreview() : ?string
    {
        return $this->preview;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('preview' => $this->preview), static function ($value) : bool {
            return $value !== null;
        });
    }
}
