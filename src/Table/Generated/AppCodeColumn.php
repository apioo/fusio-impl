<?php

namespace Fusio\Impl\Table\Generated;

enum AppCodeColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_APP_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_USER_ID;
    case CODE = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_CODE;
    case REDIRECT_URI = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_REDIRECT_URI;
    case SCOPE = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_SCOPE;
    case DATE = \Fusio\Impl\Table\Generated\AppCodeTable::COLUMN_DATE;
}