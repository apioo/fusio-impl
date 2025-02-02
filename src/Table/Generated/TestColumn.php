<?php

namespace Fusio\Impl\Table\Generated;

enum TestColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\TestTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\TestTable::COLUMN_CATEGORY_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\TestTable::COLUMN_OPERATION_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\TestTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\TestTable::COLUMN_STATUS;
    case MESSAGE = \Fusio\Impl\Table\Generated\TestTable::COLUMN_MESSAGE;
    case RESPONSE = \Fusio\Impl\Table\Generated\TestTable::COLUMN_RESPONSE;
    case URI_FRAGMENTS = \Fusio\Impl\Table\Generated\TestTable::COLUMN_URI_FRAGMENTS;
    case PARAMETERS = \Fusio\Impl\Table\Generated\TestTable::COLUMN_PARAMETERS;
    case HEADERS = \Fusio\Impl\Table\Generated\TestTable::COLUMN_HEADERS;
    case BODY = \Fusio\Impl\Table\Generated\TestTable::COLUMN_BODY;
}