<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2017 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl;

use Doctrine\DBAL\Connection;
use PSX\Framework\DisplayException;
use PSX\Http\RequestInterface;
use PSX\Http\Stream\Util;

/**
 * Logger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Logger
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param integer $routeId
     * @param integer $appId
     * @param integer $userId
     * @param string $ip
     * @param \PSX\Http\RequestInterface $request
     * @return string
     */
    public function log($routeId, $appId, $userId, $ip, RequestInterface $request)
    {
        $now  = new \DateTime();
        $path = $request->getRequestTarget();

        if (strlen($path) > 1023) {
            $path = substr($path, 0, 1023);
        }

        $this->connection->insert('fusio_log', array(
            'routeId'   => $routeId,
            'appId'     => $appId,
            'userId'    => $userId,
            'ip'        => $ip,
            'userAgent' => $request->getHeader('User-Agent'),
            'method'    => $request->getMethod(),
            'path'      => $path,
            'header'    => $this->getHeadersAsString($request),
            'body'      => $this->getBodyAsString($request),
            'date'      => $now->format('Y-m-d H:i:s'),
        ));

        return $this->connection->lastInsertId();
    }

    /**
     * @param integer $logId
     * @param string $startTime
     * @param string $endTime
     */
    public function setExecutionTime($logId, $startTime, $endTime)
    {
        list($startUsec, $startSec) = explode(' ', $startTime);
        list($endUsec, $endSec) = explode(' ', $endTime);

        $diffSec = $startSec != $endSec ? $endSec - $startSec : 0;
        $diffUsec = $endUsec - $startUsec;
        $sec = intval(($diffSec + $diffUsec) * 1000000);

        $this->connection->update('fusio_log', [
            'executionTime' => $sec,
        ], [
            'id' => $logId,
        ]);
    }

    /**
     * @param integer $logId
     * @param \Throwable $exception
     */
    public function appendError($logId, \Throwable $exception)
    {
        if ($exception instanceof DisplayException) {
            return;
        }

        $previousException = $exception->getPrevious();
        if ($previousException instanceof \Throwable) {
            $this->appendError($logId, $previousException);
        }

        $this->connection->insert('fusio_log_error', array(
            'logId'   => $logId,
            'message' => $exception->getMessage(),
            'trace'   => $exception->getTraceAsString(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
        ));
    }

    protected function getHeadersAsString(RequestInterface $request)
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

    protected function getBodyAsString(RequestInterface $request)
    {
        $body = Util::toString($request->getBody());
        if (empty($body)) {
            $body = null;
        }

        return $body;
    }
}
