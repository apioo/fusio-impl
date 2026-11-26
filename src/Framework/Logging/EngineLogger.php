<?php
/*
 * Fusio - Self-Hosted API Management for Builders.
 * For the current version and information visit <https://www.fusio-project.org/>
 *
 * Copyright (c) Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Fusio\Impl\Framework\Logging;

use Doctrine\DBAL\Connection;
use Monolog\Level;
use Monolog\Logger;

/**
 * EngineLogger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
final class EngineLogger extends Logger
{
    private TableHandler $handler;

    public function __construct(Connection $connection)
    {
        parent::__construct('fusio', [$this->handler = new TableHandler($connection, Level::Debug)]);
    }

    public function push(int $logId): void
    {
        $this->handler->setLogId($logId);
    }

    public function pop(): void
    {
        $this->handler->setLogId(null);
    }
}
