<?php

namespace Fusio\Impl\Table\Generated;

enum BundleColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_NAME;
    case CONFIG = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_CONFIG;
    case METADATA = \Fusio\Impl\Table\Generated\BundleTable::COLUMN_METADATA;
}