<?php

namespace Fusio\Impl\Table\Generated;

enum ScopeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_NAME;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_DESCRIPTION;
    case METADATA = \Fusio\Impl\Table\Generated\ScopeTable::COLUMN_METADATA;
}