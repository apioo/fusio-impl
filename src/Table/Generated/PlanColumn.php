<?php

namespace Fusio\Impl\Table\Generated;

enum PlanColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_NAME;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_DESCRIPTION;
    case PRICE = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_PRICE;
    case POINTS = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_POINTS;
    case PERIOD_TYPE = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_PERIOD_TYPE;
    case EXTERNAL_ID = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_EXTERNAL_ID;
    case METADATA = \Fusio\Impl\Table\Generated\PlanTable::COLUMN_METADATA;
}