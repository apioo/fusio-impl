<?php

namespace Fusio\Impl\Table\Generated;

enum IdentityRequestColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\IdentityRequestTable::COLUMN_ID;
    case IDENTITY_ID = \Fusio\Impl\Table\Generated\IdentityRequestTable::COLUMN_IDENTITY_ID;
    case STATE = \Fusio\Impl\Table\Generated\IdentityRequestTable::COLUMN_STATE;
    case REDIRECT_URI = \Fusio\Impl\Table\Generated\IdentityRequestTable::COLUMN_REDIRECT_URI;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\IdentityRequestTable::COLUMN_INSERT_DATE;
}