<?php

namespace Fusio\Impl\Table\Generated;

enum AgentColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_CATEGORY_ID;
    case CONNECTION_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_CONNECTION_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_STATUS;
    case TYPE = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_TYPE;
    case NAME = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_NAME;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_DESCRIPTION;
    case INTRODUCTION = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_INTRODUCTION;
    case TOOLS = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_TOOLS;
    case OUTGOING = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_OUTGOING;
    case ACTION = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_ACTION;
    case METADATA = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_METADATA;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\AgentTable::COLUMN_INSERT_DATE;
}