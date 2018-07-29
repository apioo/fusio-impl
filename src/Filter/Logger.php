<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2018 Christoph Kappestein <christoph.kappestein@gmail.com>
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

namespace Fusio\Impl\Filter;

use Doctrine\DBAL\Connection;
use Fusio\Impl\Loader\Context;
use PSX\Framework\DisplayException;
use PSX\Http\FilterChainInterface;
use PSX\Http\FilterInterface;
use PSX\Http\RequestInterface;
use PSX\Http\ResponseInterface;
use PSX\Http\Stream\Util;

/**
 * Logger
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    http://fusio-project.org
 */
class Logger implements FilterInterface
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * @var \Fusio\Impl\Loader\Context
     */
    protected $context;

    public function __construct(Connection $connection, Context $context)
    {
        $this->connection = $connection;
        $this->context    = $context;
    }

    public function handle(RequestInterface $request, ResponseInterface $response, FilterChainInterface $filterChain)
    {
        $remoteIp  = $request->getAttribute('REMOTE_ADDR') ?: '127.0.0.1';
        $startTime = microtime();

        $now  = new \DateTime();
        $path = $request->getRequestTarget();

        if (strlen($path) > 1023) {
            $path = substr($path, 0, 1023);
        }

        $this->connection->insert('fusio_log', array(
            'route_id'   => $this->context->getRouteId(),
            'app_id'     => $this->context->getAppId(),
            'user_id'    => $this->context->getUserId(),
            'ip'         => $remoteIp,
            'user_agent' => $request->getHeader('User-Agent'),
            'method'     => $request->getMethod(),
            'path'       => $path,
            'header'     => $this->getHeadersAsString($request),
            'body'       => $this->getBodyAsString($request),
            'date'       => $now->format('Y-m-d H:i:s'),
        ));

        $logId = $this->connection->lastInsertId();
        $this->context->setLogId($logId);

        try {
            $filterChain->handle($request, $response);
        } catch (\Throwable $e) {
            $this->appendError($e);

            throw $e;
        }

        $endTime = microtime();

        $this->setExecutionTime($startTime, $endTime);
    }

    /**
     * @param string $startTime
     * @param string $endTime
     */
    public function setExecutionTime($startTime, $endTime)
    {
        list($startUsec, $startSec) = explode(' ', $startTime);
        list($endUsec, $endSec) = explode(' ', $endTime);

        $diffSec  = $startSec != $endSec ? $endSec - $startSec : 0;
        $diffUsec = $endUsec - $startUsec;

        $this->connection->update('fusio_log', [
            'execution_time' => intval(($diffSec + $diffUsec) * 1000000),
        ], [
            'id' => $this->context->getLogId(),
        ]);
    }

    /**
     * @param integer $logId
     * @param \Throwable $exception
     */
    public function appendError(\Throwable $exception)
    {
        if ($exception instanceof DisplayException) {
            return;
        }

        $previousException = $exception->getPrevious();
        if ($previousException instanceof \Throwable) {
            $this->appendError($previousException);
        }

        $message = $exception->getMessage();
        if (strlen($message) > 500) {
            $message = substr($message, 0, 500);
        }

        $this->connection->insert('fusio_log_error', array(
            'log_id'  => $this->context->getLogId(),
            'message' => $message,
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
