<?php

declare(strict_types = 1);

namespace Fusio\Impl\Consumer\Model;

/**
 * @Required({"event", "endpoint"})
 */
class Event_Subscription_Create extends Event_Subscription implements \JsonSerializable
{
}
