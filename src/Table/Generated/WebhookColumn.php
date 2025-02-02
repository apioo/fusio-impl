<?php

namespace Fusio\Impl\Table\Generated;

enum WebhookColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_ID;
    case EVENT_ID = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_EVENT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_USER_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_NAME;
    case ENDPOINT = \Fusio\Impl\Table\Generated\WebhookTable::COLUMN_ENDPOINT;
}