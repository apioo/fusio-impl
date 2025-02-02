<?php

namespace Fusio\Impl\Table\Generated;

enum ScopeOperationColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ScopeOperationTable::COLUMN_ID;
    case SCOPE_ID = \Fusio\Impl\Table\Generated\ScopeOperationTable::COLUMN_SCOPE_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\ScopeOperationTable::COLUMN_OPERATION_ID;
    case ALLOW = \Fusio\Impl\Table\Generated\ScopeOperationTable::COLUMN_ALLOW;
}