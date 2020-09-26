<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"name", "url", "scopes"})
 */
class App_Create extends App implements \JsonSerializable
{
}
