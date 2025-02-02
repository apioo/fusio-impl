<?php

namespace Fusio\Impl\Table\Generated;

enum ConnectionColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_TENANT_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_CATEGORY_ID;
    case STATUS = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_NAME;
    case CLASS_ = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_CLASS;
    case CONFIG = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_CONFIG;
    case METADATA = \Fusio\Impl\Table\Generated\ConnectionTable::COLUMN_METADATA;
}