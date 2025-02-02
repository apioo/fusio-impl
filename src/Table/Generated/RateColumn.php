<?php

namespace Fusio\Impl\Table\Generated;

enum RateColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\RateTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\RateTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\RateTable::COLUMN_STATUS;
    case PRIORITY = \Fusio\Impl\Table\Generated\RateTable::COLUMN_PRIORITY;
    case NAME = \Fusio\Impl\Table\Generated\RateTable::COLUMN_NAME;
    case RATE_LIMIT = \Fusio\Impl\Table\Generated\RateTable::COLUMN_RATE_LIMIT;
    case TIMESPAN = \Fusio\Impl\Table\Generated\RateTable::COLUMN_TIMESPAN;
    case METADATA = \Fusio\Impl\Table\Generated\RateTable::COLUMN_METADATA;
}