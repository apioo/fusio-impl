<?php

namespace Fusio\Impl\Table\Generated;

enum OperationColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_ID;
    case CATEGORY_ID = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_CATEGORY_ID;
    case TAXONOMY_ID = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_TAXONOMY_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_STATUS;
    case ACTIVE = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_ACTIVE;
    case PUBLIC = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_PUBLIC;
    case STABILITY = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_STABILITY;
    case DESCRIPTION = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_DESCRIPTION;
    case HTTP_METHOD = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_HTTP_METHOD;
    case HTTP_PATH = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_HTTP_PATH;
    case HTTP_CODE = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_HTTP_CODE;
    case NAME = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_NAME;
    case PARAMETERS = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_PARAMETERS;
    case INCOMING = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_INCOMING;
    case OUTGOING = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_OUTGOING;
    case THROWS = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_THROWS;
    case ACTION = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_ACTION;
    case COSTS = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_COSTS;
    case METADATA = \Fusio\Impl\Table\Generated\OperationTable::COLUMN_METADATA;
}