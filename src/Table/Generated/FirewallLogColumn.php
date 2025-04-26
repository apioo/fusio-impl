<?php

namespace Fusio\Impl\Table\Generated;

enum FirewallLogColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\FirewallLogTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\FirewallLogTable::COLUMN_TENANT_ID;
    case IP = \Fusio\Impl\Table\Generated\FirewallLogTable::COLUMN_IP;
    case RESPONSE_CODE = \Fusio\Impl\Table\Generated\FirewallLogTable::COLUMN_RESPONSE_CODE;
    case INSERT_DATE = \Fusio\Impl\Table\Generated\FirewallLogTable::COLUMN_INSERT_DATE;
}