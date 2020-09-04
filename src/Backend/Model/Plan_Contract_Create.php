<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"userId", "planId"})
 */
class Plan_Contract_Create extends Plan_Contract implements \JsonSerializable
{
}
