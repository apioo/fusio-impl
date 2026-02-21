<?php

namespace Fusio\Impl\Table\Generated;

enum TriggerColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_CATEGORY_ID;
    case TAXONOMY_ID = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_TAXONOMY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_NAME;
    case EVENT = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_EVENT;
    case ACTION = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_ACTION;
    case METADATA = \Fusio\Impl\Table\Generated\TriggerTable::COLUMN_METADATA;
}