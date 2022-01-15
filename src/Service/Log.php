<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2021 Christoph Kappestein <christoph.kappestein@gmail.com>
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
            self::START_TIME => hrtime(),
            self::LOG_ID => null
        ];

        $now = new \DateTime();

        if (strlen($path) > 1023) {
            $path = substr($path, 0, 1023);
        }

        $this->connection->insert('fusio_log', array(
            'category_id' => $context->getCategoryId(),
            'route_id'    => $context->getRouteId(),
            'app_id'      => $context->getAppId(),
            'user_id'     => $context->getUserId(),
            'ip'          => $remoteIp,
            'user_agent'  => $userAgent,
            'method'      => $method,
            'path'        => $path,
            'header'      => $request !== null ? $this->getHeadersAsString($request) : '',
            'body'        => $request !== null ? $this->getBodyAsString($request) : '',
            'date'        => $now->format('Y-m-d H:i:s'),
        ));

        $this->stack[self::$level][self::LOG_ID] = $this->connection->lastInsertId();
    }

    public function finish(): void
    {
        $startTime = $this->stack[self::$level][self::START_TIME] ?? null;
        $logId = $this->stack[self::$level][self::LOG_ID] ?? null;

        if ($logId === null) {
            return;
        }

        self::$level--;

        $endTime = hrtime();

        [$startSec, $startUsec] = $startTime;
        [$endSec, $endUsec] = $endTime;

        $diffSec  = $startSec != $endSec ? $endSec - $startSec : 0;
        $diffUsec = $endUsec - $startUsec;

        $this->connection->update('fusio_log', [
            'execution_time' => intval(($diffSec + $diffUsec) * 1000000),
        ], [
            'id' => $logId,
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

        $this->connection->insert('fusio_log_error', array(
            'log_id'  => $logId,
            'message' => $message,
            'trace'   => $exception->getTraceAsString(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
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
