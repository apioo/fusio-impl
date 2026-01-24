<?php

namespace Fusio\Impl\Table\Generated;

enum AgentColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_USER_ID;
    case CONNECTION_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_CONNECTION_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_TENANT_ID;
    case ORIGIN = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_ORIGIN;
    case MESSAGE = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_MESSAGE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_INSERT_DATE;
}