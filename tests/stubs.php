<?php

namespace
{
    const PSX_PATH_SRC = '';
    const PSX_PATH_CACHE = '';
}

namespace PhpAmqpLib\Connection
{
    class AMQPStreamConnection
    {
    }
}

namespace PhpAmqpLib\Message
{
    class AMQPMessage
    {
        public const DELIVERY_MODE_PERSISTENT = 0;
    }
}

namespace Thrift\Transport
{
    class TSocket
    {
        public function __construct(string $host, int $port)
        {
        }
    }

    class TBufferedTransport
    {
        public function __construct($transport, int $rBufSize = 512, int $wBufSize = 512)
        {
        }
    }
}

namespace Thrift\Protocol
{
    class TBinaryProtocol
    {
        public function __construct($transport, bool $strictRead = false, bool $strictWrite = true)
        {
        }

        public function open()
        {
        }
    }
}
