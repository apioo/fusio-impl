<?php

namespace Fusio\Impl\Table\Generated;

enum IdentityColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_STATUS;
    case APP_ID = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_APP_ID;
    case ROLE_ID = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_ROLE_ID;
    case NAME = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_NAME;
    case ICON = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_ICON;
    case CLASS_ = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_CLASS;
    case CONFIG = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_CONFIG;
    case ALLOW_CREATE = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_ALLOW_CREATE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\IdentityTable::COLUMN_INSERT_DATE;
}