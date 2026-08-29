<?php

namespace Fusio\Impl\Table\Generated;

enum ActionTagColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ActionTagTable::COLUMN_ID;
    case COMMIT_ID = \Fusio\Impl\Table\Generated\ActionTagTable::COLUMN_COMMIT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\ActionTagTable::COLUMN_USER_ID;
    case VERSION = \Fusio\Impl\Table\Generated\ActionTagTable::COLUMN_VERSION;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\ActionTagTable::COLUMN_INSERT_DATE;
}