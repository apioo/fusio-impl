<?php

namespace Fusio\Impl\Table\Generated;

enum ConfigColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_TENANT_ID;
    case TYPE = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_TYPE;
    case NAME = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_NAME;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_DESCRIPTION;
    case VALUE = \Fusio\Impl\Table\Generated\ConfigTable::COLUMN_VALUE;
}