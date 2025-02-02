<?php

namespace Fusio\Impl\Table\Generated;

enum TransactionColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_TENANT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_USER_ID;
    case PLAN_ID = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_PLAN_ID;
    case TRANSACTION_ID = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_TRANSACTION_ID;
    case AMOUNT = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_AMOUNT;
    case POINTS = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_POINTS;
    case PERIOD_START = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_PERIOD_START;
    case PERIOD_END = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_PERIOD_END;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\TransactionTable::COLUMN_INSERT_DATE;
}