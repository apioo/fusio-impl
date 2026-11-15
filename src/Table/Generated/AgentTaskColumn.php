<?php

namespace Fusio\Impl\Table\Generated;

enum AgentTaskColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_ID;
    case AGENT_ID = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_AGENT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_USER_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_TENANT_ID;
    case CONNECTION_ID = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_CONNECTION_ID;
    case STATUS = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_STATUS;
    case ITEM = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_ITEM;
    case STEPS = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_STEPS;
    case MESSAGE = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_MESSAGE;
    case START_DATE = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_START_DATE;
    case END_DATE = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_END_DATE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\AgentTaskTable::COLUMN_INSERT_DATE;
}