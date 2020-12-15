<?php

declare(strict_types = 1);

namespace Fusio\Impl\System\Model;


class About_Link implements \JsonSerializable
{
    /**
     * @var string|null
     */
    protected $rel;
    /**
     * @var string|null
     */
    protected $href;
    /**
     * @param string|null $rel
     */
    public function setRel(?string $rel) : void
    {
        $this->rel = $rel;
    }
    /**
     * @return string|null
     */
    public function getRel() : ?string
    {
        return $this->rel;
    }
    /**
     * @param string|null $href
     */
    public function setHref(?string $href) : void
    {
        $this->href = $href;
    }
    /**
     * @return string|null
     */
    public function getHref() : ?string
    {
        return $this->href;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('rel' => $this->rel, 'href' => $this->href), static function ($value) : bool {
            return $value !== null;
        });
    }
}
