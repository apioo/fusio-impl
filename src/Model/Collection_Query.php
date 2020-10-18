<?php

declare(strict_types = 1);

namespace Fusio\Impl\Model;


class Collection_Query implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $startIndex;
    /**
     * @var int|null
     */
    protected $count;
    /**
     * @var string|null
     */
    protected $search;
    /**
     * @param int|null $startIndex
     */
    public function setStartIndex(?int $startIndex) : void
    {
        $this->startIndex = $startIndex;
    }
    /**
     * @return int|null
     */
    public function getStartIndex() : ?int
    {
        return $this->startIndex;
    }
    /**
     * @param int|null $count
     */
    public function setCount(?int $count) : void
    {
        $this->count = $count;
    }
    /**
     * @return int|null
     */
    public function getCount() : ?int
    {
        return $this->count;
    }
    /**
     * @param string|null $search
     */
    public function setSearch(?string $search) : void
    {
        $this->search = $search;
    }
    /**
     * @return string|null
     */
    public function getSearch() : ?string
    {
        return $this->search;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('startIndex' => $this->startIndex, 'count' => $this->count, 'search' => $this->search), static function ($value) : bool {
            return $value !== null;
        });
    }
}
