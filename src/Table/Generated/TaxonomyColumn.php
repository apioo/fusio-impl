<?php

namespace Fusio\Impl\Table\Generated;

enum TaxonomyColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_ID;
    case PARENT_ID = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_PARENT_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_NAME;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\TaxonomyTable::COLUMN_INSERT_DATE;
}