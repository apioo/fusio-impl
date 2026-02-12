<?php

namespace Fusio\Impl\Table\Generated;

enum CronjobColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_TENANT_ID;
    case TAXONOMY_ID = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_TAXONOMY_ID;
    case STATUS = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_NAME;
    case CRON = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_CRON;
    case ACTION = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_ACTION;
    case EXECUTE_DATE = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_EXECUTE_DATE;
    case EXIT_CODE = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_EXIT_CODE;
    case METADATA = \Fusio\Impl\Table\Generated\CronjobTable::COLUMN_METADATA;
}