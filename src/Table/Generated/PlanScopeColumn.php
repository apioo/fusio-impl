<?php

namespace Fusio\Impl\Table\Generated;

enum PlanScopeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\PlanScopeTable::COLUMN_ID;
    case PLAN_ID = \Fusio\Impl\Table\Generated\PlanScopeTable::COLUMN_PLAN_ID;
    case SCOPE_ID = \Fusio\Impl\Table\Generated\PlanScopeTable::COLUMN_SCOPE_ID;
}