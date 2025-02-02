<?php

namespace Fusio\Impl\Table\Generated;

enum SchemaColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_CATEGORY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_NAME;
    case SOURCE = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_SOURCE;
    case FORM = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_FORM;
    case METADATA = \Fusio\Impl\Table\Generated\SchemaTable::COLUMN_METADATA;
}