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
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\NormalizerFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use function json_encode;

/**
 * TableHandler
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class TableHandler extends AbstractProcessingHandler
{
    private ?int $logId = null;

    public function __construct(private readonly Connection $connection, int|string|Level $level = Level::Debug, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
    }

    public function setLogId(?int $logId): void
    {
        $this->logId = $logId;
    }

    protected function write(LogRecord $record): void
    {
        if ($this->logId === null) {
            return;
        }

        $message = $record->message;
        if (strlen($message) > 500) {
            $message = substr($message, 0, 500);
        }

        $this->connection->insert('fusio_log_message', [
            'log_id' => $this->logId,
            'level' => $record->level->toRFC5424Level(),
            'message' => $message,
            'context' => json_encode($record->context),
            'insert_date' => $record->datetime->format('Y-m-d H:i:s'),
        ]);
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new NormalizerFormatter();
    }
}
