<?php

namespace Fusio\Impl\Table\Generated;

enum UserColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_ID;
    case IDENTITY_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_IDENTITY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_TENANT_ID;
    case ROLE_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_ROLE_ID;
    case PLAN_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_PLAN_ID;
    case STATUS = \Fusio\Impl\Table\Generated\UserTable::COLUMN_STATUS;
    case REMOTE_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_REMOTE_ID;
    case EXTERNAL_ID = \Fusio\Impl\Table\Generated\UserTable::COLUMN_EXTERNAL_ID;
    case NAME = \Fusio\Impl\Table\Generated\UserTable::COLUMN_NAME;
    case EMAIL = \Fusio\Impl\Table\Generated\UserTable::COLUMN_EMAIL;
    case PASSWORD = \Fusio\Impl\Table\Generated\UserTable::COLUMN_PASSWORD;
    case POINTS = \Fusio\Impl\Table\Generated\UserTable::COLUMN_POINTS;
    case TOKEN = \Fusio\Impl\Table\Generated\UserTable::COLUMN_TOKEN;
    case METADATA = \Fusio\Impl\Table\Generated\UserTable::COLUMN_METADATA;
    case DATE = \Fusio\Impl\Table\Generated\UserTable::COLUMN_DATE;
}