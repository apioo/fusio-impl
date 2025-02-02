<?php

namespace Fusio\Impl\Table\Generated;

enum AuditColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_TENANT_ID;
    case APP_ID = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_APP_ID;
    case USER_ID = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_USER_ID;
    case REF_ID = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_REF_ID;
    case EVENT = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_EVENT;
    case IP = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_IP;
    case MESSAGE = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_MESSAGE;
    case CONTENT = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_CONTENT;
    case DATE = \Fusio\Impl\Table\Generated\AuditTable::COLUMN_DATE;
}