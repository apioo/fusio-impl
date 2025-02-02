<?php

namespace Fusio\Impl\Table\Generated;

enum RoleScopeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\RoleScopeTable::COLUMN_ID;
    case ROLE_ID = \Fusio\Impl\Table\Generated\RoleScopeTable::COLUMN_ROLE_ID;
    case SCOPE_ID = \Fusio\Impl\Table\Generated\RoleScopeTable::COLUMN_SCOPE_ID;
}