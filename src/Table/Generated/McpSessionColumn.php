<?php

namespace Fusio\Impl\Table\Generated;

enum McpSessionColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\McpSessionTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\McpSessionTable::COLUMN_TENANT_ID;
    case SESSION_ID = \Fusio\Impl\Table\Generated\McpSessionTable::COLUMN_SESSION_ID;
    case DATA = \Fusio\Impl\Table\Generated\McpSessionTable::COLUMN_DATA;
}