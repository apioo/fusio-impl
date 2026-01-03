<?php

namespace Fusio\Impl\Table\Generated;

enum SchemaCommitColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_ID;
    case SCHEMA_ID = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_SCHEMA_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_USER_ID;
    case PREV_HASH = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_PREV_HASH;
    case COMMIT_HASH = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_COMMIT_HASH;
    case SOURCE = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_SOURCE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\SchemaCommitTable::COLUMN_INSERT_DATE;
}