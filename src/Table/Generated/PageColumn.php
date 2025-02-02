<?php

namespace Fusio\Impl\Table\Generated;

enum PageColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\PageTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\PageTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\PageTable::COLUMN_STATUS;
    case TITLE = \Fusio\Impl\Table\Generated\PageTable::COLUMN_TITLE;
    case SLUG = \Fusio\Impl\Table\Generated\PageTable::COLUMN_SLUG;
    case CONTENT = \Fusio\Impl\Table\Generated\PageTable::COLUMN_CONTENT;
    case METADATA = \Fusio\Impl\Table\Generated\PageTable::COLUMN_METADATA;
    case DATE = \Fusio\Impl\Table\Generated\PageTable::COLUMN_DATE;
}