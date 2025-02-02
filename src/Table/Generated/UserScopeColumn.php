<?php

namespace Fusio\Impl\Table\Generated;

enum UserScopeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\UserScopeTable::COLUMN_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\UserScopeTable::COLUMN_USER_ID;
    case SCOPE_ID = \Fusio\Impl\Table\Generated\UserScopeTable::COLUMN_SCOPE_ID;
}