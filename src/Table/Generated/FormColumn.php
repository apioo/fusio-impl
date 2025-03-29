<?php

namespace Fusio\Impl\Table\Generated;

enum FormColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\FormTable::COLUMN_ID;
    case OPERATION_ID = \Fusio\Impl\Table\Generated\FormTable::COLUMN_OPERATION_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\FormTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\FormTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\FormTable::COLUMN_NAME;
    case UI_SCHEMA = \Fusio\Impl\Table\Generated\FormTable::COLUMN_UI_SCHEMA;
    case METADATA = \Fusio\Impl\Table\Generated\FormTable::COLUMN_METADATA;
}