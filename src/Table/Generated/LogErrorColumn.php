<?php

namespace Fusio\Impl\Table\Generated;

enum LogErrorColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_ID;
    case LOG_ID = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_LOG_ID;
    case MESSAGE = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_MESSAGE;
    case TRACE = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_TRACE;
    case FILE = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_FILE;
    case LINE = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_LINE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\LogErrorTable::COLUMN_INSERT_DATE;
}