<?php

declare(strict_types = 1);

namespace Fusio\Impl\Backend\Model;

/**
 * @Required({"name", "cron", "action"})
 */
class Cronjob_Create extends Cronjob implements \JsonSerializable
{
}
