<?php

namespace Fusio\Impl\Table\Generated;

enum RateAllocationColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_ID;
    case RATE_ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_RATE_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_OPERATION_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_APP_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_USER_ID;
    case PLAN_ID = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_PLAN_ID;
    case AUTHENTICATED = \Fusio\Impl\Table\Generated\RateAllocationTable::COLUMN_AUTHENTICATED;
}