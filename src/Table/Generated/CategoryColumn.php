<?php

namespace Fusio\Impl\Table\Generated;

enum CategoryColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\CategoryTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\CategoryTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\CategoryTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\CategoryTable::COLUMN_NAME;
}