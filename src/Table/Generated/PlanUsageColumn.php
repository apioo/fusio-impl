<?php

namespace Fusio\Impl\Table\Generated;

enum PlanUsageColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_OPERATION_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_USER_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_APP_ID;
    case POINTS = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_POINTS;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\PlanUsageTable::COLUMN_INSERT_DATE;
}