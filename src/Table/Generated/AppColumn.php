<?php

namespace Fusio\Impl\Table\Generated;

enum AppColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AppTable::COLUMN_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AppTable::COLUMN_USER_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AppTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\AppTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\AppTable::COLUMN_NAME;
    case URL = \Fusio\Impl\Table\Generated\AppTable::COLUMN_URL;
    case PARAMETERS = \Fusio\Impl\Table\Generated\AppTable::COLUMN_PARAMETERS;
    case APP_KEY = \Fusio\Impl\Table\Generated\AppTable::COLUMN_APP_KEY;
    case APP_SECRET = \Fusio\Impl\Table\Generated\AppTable::COLUMN_APP_SECRET;
    case METADATA = \Fusio\Impl\Table\Generated\AppTable::COLUMN_METADATA;
    case DATE = \Fusio\Impl\Table\Generated\AppTable::COLUMN_DATE;
}