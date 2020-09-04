<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;


class Action_Index implements \JsonSerializable
{
    /**
     * @var array<Action>|null
     */
    protected $actions;
    /**
     * @param array<Action>|null $actions
     */
    public function setActions(?array $actions) : void
    {
        $this->actions = $actions;
    }
    /**
     * @return array<Action>|null
     */
    public function getActions() : ?array
    {
        return $this->actions;
    }
    public function jsonSerialize()
    {
        return (object) array_filter(array('actions' => $this->actions), static function ($value) : bool {
            return $value !== null;
        });
    }
}
