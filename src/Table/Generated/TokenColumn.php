<?php

namespace Fusio\Impl\Table\Generated;

enum TokenColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_CATEGORY_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_APP_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_USER_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_NAME;
    case TOKEN = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_TOKEN;
    case REFRESH = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_REFRESH;
    case SCOPE = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_SCOPE;
    case IP = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_IP;
    case EXPIRE = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_EXPIRE;
    case DATE = \Fusio\Impl\Table\Generated\TokenTable::COLUMN_DATE;
}