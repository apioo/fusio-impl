<?php

namespace Fusio\Impl\Table\Generated;

enum SchemaTagColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\SchemaTagTable::COLUMN_ID;
    case COMMIT_ID = \Fusio\Impl\Table\Generated\SchemaTagTable::COLUMN_COMMIT_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\SchemaTagTable::COLUMN_USER_ID;
    case VERSION = \Fusio\Impl\Table\Generated\SchemaTagTable::COLUMN_VERSION;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\SchemaTagTable::COLUMN_INSERT_DATE;
}