<?php

namespace Fusio\Impl\Table\Generated;

enum UserGrantColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\UserGrantTable::COLUMN_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\UserGrantTable::COLUMN_USER_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\UserGrantTable::COLUMN_APP_ID;
    case ALLOW = \Fusio\Impl\Table\Generated\UserGrantTable::COLUMN_ALLOW;
    case DATE = \Fusio\Impl\Table\Generated\UserGrantTable::COLUMN_DATE;
}