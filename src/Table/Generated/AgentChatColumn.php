<?php

namespace Fusio\Impl\Table\Generated;

enum AgentChatColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_USER_ID;
    case CONNECTION_ID = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_CONNECTION_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_TENANT_ID;
    case TYPE = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_TYPE;
    case MESSAGE = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_MESSAGE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\AgentChatTable::COLUMN_INSERT_DATE;
}