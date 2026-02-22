<?php

namespace Fusio\Impl\Table\Generated;

enum AgentMessageColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_ID;
    case AGENT_ID = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_AGENT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_USER_ID;
    case PARENT_ID = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_PARENT_ID;
    case ORIGIN = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_ORIGIN;
    case CONTENT = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_CONTENT;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\AgentMessageTable::COLUMN_INSERT_DATE;
}