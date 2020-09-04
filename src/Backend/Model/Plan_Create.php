<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"name", "price"})
 */
class Plan_Create extends Plan implements \JsonSerializable
{
}
