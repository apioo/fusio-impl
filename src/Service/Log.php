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

namespace Fusio\Impl\Service;

use Doctrine\DBAL\Connection as DBALConnection;
use Fusio\Impl\Framework\Loader\Context;
use Fusio\Impl\Table;
use PSX\Framework\DisplayException;
use PSX\Http\RequestInterface;
use PSX\Http\Stream\Util;

/**
 * Log
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link    https://www.fusio-project.org
 */
class Log
{
    public const START_TIME = 0;
    public const LOG_ID = 1;

    private static int $level = -1;

    private DBALConnection $connection;

    /**
     * @var array<array{0: float, 1: int|null}>
     */
    private array $stack;

    public function __construct(DBALConnection $connection)
    {
        $this->connection = $connection;
        $this->stack = [];
    }

    public function log(string $remoteIp, string $method, string $path, string $userAgent, Context $context, ?RequestInterface $request = null): void
    {
        self::$level++;

        $this->stack[self::$level] = [
            self::START_TIME => hrtime(true),
            self::LOG_ID => null
        ];

        $now = new \DateTime();

        if (strlen($userAgent) > 255) {
            $userAgent = substr($userAgent, 0, 255);
        }

        if (strlen($path) > 1023) {
            $path = substr($path, 0, 1023);
        }

        $this->connection->insert(Table\Generated\LogTable::NAME, array(
            Table\Generated\LogTable::COLUMN_TENANT_ID => $context->getTenantId(),
            Table\Generated\LogTable::COLUMN_CATEGORY_ID => $context->getOperation()->getCategoryId(),
            Table\Generated\LogTable::COLUMN_OPERATION_ID => $context->getOperation()->getId(),
            Table\Generated\LogTable::COLUMN_APP_ID => $context->getAppId(),
            Table\Generated\LogTable::COLUMN_USER_ID => $context->getUserId(),
            Table\Generated\LogTable::COLUMN_IP => $remoteIp,
            Table\Generated\LogTable::COLUMN_USER_AGENT => $userAgent,
            Table\Generated\LogTable::COLUMN_METHOD => $method,
            Table\Generated\LogTable::COLUMN_PATH => $path,
            Table\Generated\LogTable::COLUMN_HEADER => $request !== null ? $this->getHeadersAsString($request) : '',
            Table\Generated\LogTable::COLUMN_BODY => $request !== null ? $this->getBodyAsString($request) : '',
            Table\Generated\LogTable::COLUMN_DATE => $now->format('Y-m-d H:i:s'),
        ));

        $this->stack[self::$level][self::LOG_ID] = (int) $this->connection->lastInsertId();
    }

    public function finish(int $responseCode): void
    {
        $startTime = $this->stack[self::$level][self::START_TIME] ?? null;
        $logId = $this->stack[self::$level][self::LOG_ID] ?? null;

        if ($startTime === null || $logId === null) {
            return;
        }

        self::$level--;

        $endTime = hrtime(true);

        $this->connection->update(Table\Generated\LogTable::NAME, [
            Table\Generated\LogTable::COLUMN_RESPONSE_CODE => $responseCode,
            Table\Generated\LogTable::COLUMN_EXECUTION_TIME => (int) (($endTime - $startTime) / 1e+6),
        ], [
            Table\Generated\LogTable::COLUMN_ID => $logId,
        ]);
    }

    public function error(\Throwable $exception): void
    {
        if ($exception instanceof DisplayException) {
            return;
        }

        $logId = $this->stack[self::$level][self::LOG_ID] ?? null;

        if ($logId === null) {
            return;
        }

        $previousException = $exception->getPrevious();
        if ($previousException instanceof \Throwable) {
            $this->error($previousException);
        }

        $message = $exception->getMessage();
        if (strlen($message) > 500) {
            $message = substr($message, 0, 500);
        }

        $this->connection->insert(Table\Generated\LogErrorTable::NAME, [
            Table\Generated\LogErrorTable::COLUMN_LOG_ID => $logId,
            Table\Generated\LogErrorTable::COLUMN_MESSAGE => $message,
            Table\Generated\LogErrorTable::COLUMN_TRACE => $exception->getTraceAsString(),
            Table\Generated\LogErrorTable::COLUMN_FILE => $exception->getFile(),
            Table\Generated\LogErrorTable::COLUMN_LINE => $exception->getLine(),
            Table\Generated\LogErrorTable::COLUMN_INSERT_DATE => date('Y-m-d H:i:s'),
        ]);
    }

    protected function getHeadersAsString(RequestInterface $request): string
    {
        $headers = $request->getHeaders();
        $result  = '';

        foreach ($headers as $name => $value) {
            $name = strtr($name, '-', ' ');
            $name = strtr(ucwords(strtolower($name)), ' ', '-');

            $result.= $name . ': ' . implode(', ', $value) . "\n";
        }

        return rtrim($result);
    }

    protected function getBodyAsString(RequestInterface $request): ?string
    {
        $body = Util::toString($request->getBody());
        if (empty($body)) {
            $body = null;
        }

        return $body;
    }
}
