<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"name", "url", "scopes"})
 */
class App_Update extends App implements \JsonSerializable
{
}
