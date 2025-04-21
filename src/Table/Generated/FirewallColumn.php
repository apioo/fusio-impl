<?php

namespace Fusio\Impl\Table\Generated;

enum FirewallColumn : string implements \PSX\Sql\ColumnInterface
{
    case ID = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_ID;
    case TENANT_ID = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_TENANT_ID;
    case STATUS = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_STATUS;
    case NAME = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_NAME;
    case TYPE = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_TYPE;
    case IP = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_IP;
    case MASK = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_MASK;
    case EXPIRE = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_EXPIRE;
    case METADATA = \Fusio\Impl\Table\Generated\FirewallTable::COLUMN_METADATA;
}