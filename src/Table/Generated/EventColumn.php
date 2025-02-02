<?php

namespace Fusio\Impl\Table\Generated;

enum EventColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\EventTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\EventTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\EventTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\EventTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\EventTable::COLUMN_NAME;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\EventTable::COLUMN_DESCRIPTION;
    case EVENT_SCHEMA = \Fusio\Impl\Table\Generated\EventTable::COLUMN_EVENT_SCHEMA;
    case METADATA = \Fusio\Impl\Table\Generated\EventTable::COLUMN_METADATA;
}