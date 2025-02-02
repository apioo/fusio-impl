<?php

namespace Fusio\Impl\Table\Generated;

enum LogColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_TENANT_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_CATEGORY_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_OPERATION_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_APP_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\LogTable::COLUMN_USER_ID;
    case IP = \Fusio\Impl\Table\Generated\LogTable::COLUMN_IP;
    case USER_AGENT = \Fusio\Impl\Table\Generated\LogTable::COLUMN_USER_AGENT;
    case METHOD = \Fusio\Impl\Table\Generated\LogTable::COLUMN_METHOD;
    case PATH = \Fusio\Impl\Table\Generated\LogTable::COLUMN_PATH;
    case HEADER = \Fusio\Impl\Table\Generated\LogTable::COLUMN_HEADER;
    case BODY = \Fusio\Impl\Table\Generated\LogTable::COLUMN_BODY;
    case EXECUTION_TIME = \Fusio\Impl\Table\Generated\LogTable::COLUMN_EXECUTION_TIME;
    case DATE = \Fusio\Impl\Table\Generated\LogTable::COLUMN_DATE;
}