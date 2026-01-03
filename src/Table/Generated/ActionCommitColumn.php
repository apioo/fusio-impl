<?php

namespace Fusio\Impl\Table\Generated;

enum ActionCommitColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_ID;
    case ACTION_ID = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_ACTION_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_USER_ID;
    case PREV_HASH = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_PREV_HASH;
    case COMMIT_HASH = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_COMMIT_HASH;
    case CONFIG = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_CONFIG;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\ActionCommitTable::COLUMN_INSERT_DATE;
}