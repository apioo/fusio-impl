<?php

namespace Fusio\Impl\Table\Generated;

enum WebhookResponseColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_ID;
    case WEBHOOK_ID = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_WEBHOOK_ID;
    case STATUS = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_STATUS;
    case ATTEMPTS = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_ATTEMPTS;
    case CODE = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_CODE;
    case BODY = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_BODY;
    case EXECUTE_DATE = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_EXECUTE_DATE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\WebhookResponseTable::COLUMN_INSERT_DATE;
}