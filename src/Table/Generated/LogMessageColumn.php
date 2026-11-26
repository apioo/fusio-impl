<?php

namespace Fusio\Impl\Table\Generated;

enum LogMessageColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_ID;
    case LOG_ID = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_LOG_ID;
    case LEVEL = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_LEVEL;
    case MESSAGE = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_MESSAGE;
    case CONTEXT = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_CONTEXT;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\LogMessageTable::COLUMN_INSERT_DATE;
}