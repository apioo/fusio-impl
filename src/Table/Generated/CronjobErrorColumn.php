<?php

namespace Fusio\Impl\Table\Generated;

enum CronjobErrorColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_ID;
    case CRONJOB_ID = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_CRONJOB_ID;
    case MESSAGE = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_MESSAGE;
    case TRACE = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_TRACE;
    case FILE = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_FILE;
    case LINE = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_LINE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\CronjobErrorTable::COLUMN_INSERT_DATE;
}