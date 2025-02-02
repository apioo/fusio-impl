<?php

namespace Fusio\Impl\Table\Generated;

enum RoleColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\RoleTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\RoleTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\RoleTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\RoleTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\RoleTable::COLUMN_NAME;
}