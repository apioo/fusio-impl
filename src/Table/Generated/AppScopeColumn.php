<?php

namespace Fusio\Impl\Table\Generated;

enum AppScopeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AppScopeTable::COLUMN_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\AppScopeTable::COLUMN_APP_ID;
    case SCOPE_ID = \Fusio\Impl\Table\Generated\AppScopeTable::COLUMN_SCOPE_ID;
}