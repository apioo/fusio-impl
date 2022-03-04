<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2022 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
 * @license http://www.gnu.org/licenses/agpl-3.0
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

        if (strlen($path) > 1023) {
            $path = substr($path, 0, 1023);
        }

        $this->connection->insert(Table\Generated\LogTable::NAME, array(
            Table\Generated\LogTable::COLUMN_CATEGORY_ID => $context->getCategoryId(),
            Table\Generated\LogTable::COLUMN_ROUTE_ID => $context->getRouteId(),
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

        $this->stack[self::$level][self::LOG_ID] = $this->connection->lastInsertId();
    }

    public function finish(): void
    {
        $startTime = $this->stack[self::$level][self::START_TIME] ?? null;
        $logId = $this->stack[self::$level][self::LOG_ID] ?? null;

        if ($startTime === null || $logId === null) {
            return;
        }

        self::$level--;

        $endTime = hrtime(true);

        $this->connection->update(Table\Generated\LogTable::NAME, [
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

        $this->connection->insert(Table\Generated\LogErrorTable::NAME, array(
            Table\Generated\LogErrorTable::COLUMN_LOG_ID => $logId,
            Table\Generated\LogErrorTable::COLUMN_MESSAGE => $message,
            Table\Generated\LogErrorTable::COLUMN_TRACE => $exception->getTraceAsString(),
            Table\Generated\LogErrorTable::COLUMN_FILE => $exception->getFile(),
            Table\Generated\LogErrorTable::COLUMN_LINE => $exception->getLine(),
        ));
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
