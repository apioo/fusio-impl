<?php

namespace Fusio\Impl\Table\Generated;

enum ActionColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_CATEGORY_ID;
    case TAXONOMY_ID = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_TAXONOMY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_NAME;
    case CLASS_ = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_CLASS;
    case ASYNC = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_ASYNC;
    case CONFIG = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_CONFIG;
    case METADATA = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_METADATA;
    case DATE = \Fusio\Impl\Table\Generated\ActionTable::COLUMN_DATE;
}